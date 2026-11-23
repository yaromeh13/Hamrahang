package ir.hamrahang.tv.data.playlist

import android.content.Context
import ir.hamrahang.tv.data.auth.AuthSessionStore
import ir.hamrahang.tv.data.favorite.UacAction
import ir.hamrahang.tv.data.favorite.UacRequest
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.dao.PlaylistDao
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.local.entity.PlaylistItemEntity
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import ir.hamrahang.tv.playback.PlaybackItem
import java.util.UUID
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock

class PlaylistRepository(context: Context) {
    private val appContext = context.applicationContext
    private val dao: PlaylistDao = DatabaseProvider.database().playlistDao()

    private fun ownerId(): String = AuthSessionStore.userId(appContext)
    private fun token(): String? = AuthSessionStore.accessToken(appContext)?.takeIf { it.isNotBlank() }

    suspend fun getPlaylists(): List<PlaylistEntity> {
        val owner = ownerId()
        if (owner.isBlank()) return emptyList()
        return dao.getPlaylists(owner)
    }

    fun observePlaylists(): Flow<List<PlaylistEntity>> =
        dao.observeAllPlaylists().map { playlists ->
            val owner = ownerId()
            if (owner.isBlank()) emptyList()
            else playlists.filter { it.ownerUserId == owner }
        }

    suspend fun getPlaylist(localId: String): PlaylistEntity? =
        dao.getPlaylist(localId)

    /** Resolve bookmarked public playlists; use Room cache when offline. */
    suspend fun getFavoritePlaylists(remoteIds: List<String>): List<PlaylistEntity> {
        return remoteIds.distinct().mapNotNull { remoteId ->
            val cached = dao.getAnyByRemoteId(remoteId)
            if (cached != null) cached else runCatching {
                val dto = ApiClient.api.getPlaylist(remoteId).playlist
                    ?: return@runCatching null
                val playlist = PlaylistEntity(
                    localId = "remote_$remoteId",
                    remoteId = remoteId,
                    ownerUserId = dto.owner?.id?.toString().orEmpty(),
                    name = dto.name?.takeIf { it.isNotBlank() } ?: "Playlist",
                    thumbnail = dto.meta?.tmb,
                    itemCount = dto.meta?.nci ?: 0,
                    isPublic = dto.isPublic ?: true,
                    pendingSync = false
                )
                dao.upsert(playlist)
                syncRemoteItems(playlist, dto)
                playlist
            }.getOrNull()
        }
    }

    suspend fun getPlaylistContent(localId: String): List<PlaylistMedia> {
        val rows = dao.getItems(localId)
        val result = ArrayList<PlaylistMedia>(rows.size)
        rows.forEach { row ->
            try {
                when (row.itemType) {
                    "song" -> result.add(PlaylistMedia.SongItem(SongRepository().getSong(row.itemId)))
                    "podcast" -> result.add(PlaylistMedia.PodcastItem(PodcastRepository().getPodcast(row.itemId)))
                    "musicvideo" -> result.add(PlaylistMedia.MusicVideoItem(MusicVideoRepository().getMusicVideo(row.itemId)))
                    "video" -> result.add(PlaylistMedia.VideoItem(VideoRepository().getVideo(row.itemId)))
                }
            } catch (error: CancellationException) {
                throw error
            } catch (_: Exception) {
                // One unavailable item must not block the rest of the cached playlist.
            }
        }
        return result
    }

    suspend fun createPlaylist(input: String): PlaylistEntity {
        val name = input.trim()
        require(name.isNotEmpty()) { "missing_name" }
        val owner = ownerId()
        require(owner.isNotBlank() && AuthSessionStore.isLoggedIn(appContext)) { "login_required" }

        val local = PlaylistEntity(
            localId = UUID.randomUUID().toString(),
            ownerUserId = owner,
            name = name,
            pendingSync = true
        )
        dao.upsert(local)

        val accessToken = token() ?: return local
        return try {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = "Bearer $accessToken",
                body = UacRequest(UacAction(a = "create_playlist", name = name))
            )
            when {
                response.ok == true && response.playlist?.playlist?.id != null ->
                    applyServerPlaylist(local, response.playlist.playlist)
                response.error == "playlist_limit" -> {
                    dao.deletePlaylist(local.localId)
                    throw IllegalStateException("playlist_limit")
                }
                response.error == "missing_name" -> {
                    dao.deletePlaylist(local.localId)
                    throw IllegalStateException("missing_name")
                }
                else -> local
            }
        } catch (error: IllegalStateException) {
            throw error
        } catch (_: Exception) {
            local
        }
    }

    suspend fun addItem(playlist: PlaylistEntity, item: PlaybackItem): Boolean {
        val type = item.contentType.apiKey
        require(type in PLAYABLE_TYPES) { "unsupported_item_type" }
        val publicId = item.id.toLongOrNull()?.takeIf { it > 0L }
            ?: throw IllegalArgumentException("invalid_item_id")

        val items = dao.getItems(playlist.localId)
        if (items.any { it.itemType == type && it.itemId == publicId.toString() }) return false

        val inserted = dao.insertItem(
            PlaylistItemEntity(
                localPlaylistId = playlist.localId,
                itemType = type,
                itemId = publicId.toString(),
                position = items.size
            )
        )
        if (inserted == -1L) return false
        dao.updateItemCount(playlist.localId, playlist.itemCount + 1, System.currentTimeMillis())

        val remoteId = playlist.remoteId?.toLongOrNull() ?: return true
        val accessToken = token() ?: return true
        try {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = "Bearer $accessToken",
                body = UacRequest(
                    UacAction(
                        a = "add_playlist_item",
                        playlist_id = remoteId,
                        type = type,
                        id = publicId
                    )
                )
            )
            if (response.ok == true) {
                dao.markItemSynced(playlist.localId, type, publicId.toString())
                if (response.exists == true) {
                    dao.updateItemCount(playlist.localId, playlist.itemCount, System.currentTimeMillis())
                }
            } else if (response.error == "item_limit") {
                dao.deleteItem(playlist.localId, type, publicId.toString())
                dao.updateItemCount(playlist.localId, playlist.itemCount, System.currentTimeMillis())
                throw IllegalStateException("item_limit")
            }
        } catch (error: IllegalStateException) {
            throw error
        } catch (_: Exception) {
            // Keep the local item and retry it after connectivity returns.
        }
        return true
    }

    suspend fun refreshAndSync(): Boolean = syncMutex.withLock {
        val owner = ownerId()
        val accessToken = token()
        if (owner.isBlank() || accessToken == null) return@withLock false
        val authorization = "Bearer $accessToken"

        try {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = authorization,
                body = UacRequest(UacAction(a = "get_playlists"))
            )
            if (response.error != null || response.playlists == null) return@withLock false

            response.playlists.forEach { dto ->
                val remoteId = dto.id?.toString() ?: return@forEach
                val existing = dao.getByRemoteId(remoteId, owner)
                val local = existing ?: PlaylistEntity(
                    localId = UUID.randomUUID().toString(),
                    remoteId = remoteId,
                    ownerUserId = owner,
                    name = dto.name.orEmpty(),
                    pendingSync = false
                )
                val updated = local.copy(
                    remoteId = remoteId,
                    name = dto.name?.takeIf { it.isNotBlank() } ?: local.name,
                    thumbnail = dto.meta?.tmb ?: local.thumbnail,
                    itemCount = dto.meta?.nci ?: local.itemCount,
                    isPublic = dto.isPublic ?: local.isPublic,
                    pendingSync = false,
                    updatedAt = System.currentTimeMillis()
                )
                dao.upsert(updated)
                syncRemoteItems(updated, dto)
                val unsyncedCount = dao.getUnsyncedItems(updated.localId).size
                if (unsyncedCount > 0) {
                    dao.updateItemCount(
                        updated.localId,
                        updated.itemCount + unsyncedCount,
                        System.currentTimeMillis()
                    )
                }
            }

            syncPendingPlaylists(authorization, owner)
            syncPendingItems(authorization, owner)
            true
        } catch (_: Exception) {
            false
        }
    }

    private suspend fun syncPendingPlaylists(authorization: String, owner: String) {
        val pending = dao.getPendingPlaylists(owner)
        if (pending.isEmpty()) return

        val payload = pending.map { playlist ->
            val items = dao.getItems(playlist.localId).mapNotNull { row ->
                val id = row.itemId.toLongOrNull() ?: return@mapNotNull null
                PlaylistSyncItem(type = row.itemType, id = id)
            }
            PlaylistSyncInput(local_id = playlist.localId, name = playlist.name, items = items)
        }

        val response = ApiClient.api.favoriteAccountAction(
            authorization = authorization,
            body = UacRequest(UacAction(a = "sync_playlists", playlists = payload))
        )
        if (response.ok != true) return

        response.created.orEmpty().forEach { created ->
            val localId = created.local_id ?: return@forEach
            val dto = created.playlist?.playlist ?: return@forEach
            val local = dao.getPlaylist(localId) ?: return@forEach
            applyServerPlaylist(local, dto)
            if (created.capped == true) {
                dao.markItemsSyncedBefore(localId, dto.meta?.nci ?: 0)
                val remainingLocalItems = dao.getUnsyncedItems(localId).size
                dao.updateItemCount(
                    localId,
                    (dto.meta?.nci ?: 0) + remainingLocalItems,
                    System.currentTimeMillis()
                )
            } else {
                dao.markAllItemsSynced(localId)
            }
        }
    }

    private suspend fun syncPendingItems(authorization: String, owner: String) {
        dao.getPlaylists(owner).forEach { playlist ->
            val remoteId = playlist.remoteId?.toLongOrNull() ?: return@forEach
            dao.getUnsyncedItems(playlist.localId).forEach itemLoop@{ row ->
                val id = row.itemId.toLongOrNull() ?: return@itemLoop
                val response = ApiClient.api.favoriteAccountAction(
                    authorization = authorization,
                    body = UacRequest(
                        UacAction(
                            a = "add_playlist_item",
                            playlist_id = remoteId,
                            type = row.itemType,
                            id = id
                        )
                    )
                )
                if (response.ok == true) {
                    dao.markItemSynced(playlist.localId, row.itemType, row.itemId)
                    if (response.exists == true) {
                        val currentCount = dao.getPlaylist(playlist.localId)?.itemCount ?: 0
                        dao.updateItemCount(
                            playlist.localId,
                            (currentCount - 1).coerceAtLeast(0),
                            System.currentTimeMillis()
                        )
                    }
                } else if (response.error == "item_limit") {
                    // Keep the local item rather than silently discarding offline data.
                    return@itemLoop
                }
            }
        }
    }

    private suspend fun syncRemoteItems(
        playlist: PlaylistEntity,
        dto: PlaylistDto
    ) {
        val remoteItems = buildList {
            dto.songs.orEmpty().forEachIndexed { index, item ->
                val id = item.id?.toString() ?: return@forEachIndexed
                add(
                    PlaylistItemEntity(
                        localPlaylistId = playlist.localId,
                        itemType = if (item.meta?.type == "podcast") "podcast" else "song",
                        itemId = id,
                        position = item.position ?: index,
                        synced = true
                    )
                )
            }
            dto.videos.orEmpty().forEachIndexed { index, item ->
                val id = item.id?.toString() ?: return@forEachIndexed
                add(
                    PlaylistItemEntity(
                        localPlaylistId = playlist.localId,
                        itemType = if (item.meta?.type == "musicvideo") "musicvideo" else "video",
                        itemId = id,
                        position = item.position ?: (dto.songs.orEmpty().size + index),
                        synced = true
                    )
                )
            }
        }
        if (remoteItems.isNotEmpty()) dao.upsertItems(remoteItems)
    }

    private suspend fun applyServerPlaylist(
        local: PlaylistEntity,
        dto: PlaylistDto
    ): PlaylistEntity {
        val remoteId = dto.id?.toString() ?: return local
        val updated = local.copy(
            remoteId = remoteId,
            name = dto.name?.takeIf { it.isNotBlank() } ?: local.name,
            thumbnail = dto.meta?.tmb ?: local.thumbnail,
            itemCount = dto.meta?.nci ?: local.itemCount,
            isPublic = dto.isPublic ?: local.isPublic,
            pendingSync = false,
            updatedAt = System.currentTimeMillis()
        )
        dao.upsert(updated)
        return updated
    }

    companion object {
        private val syncMutex = Mutex()
        private val PLAYABLE_TYPES = setOf("song", "podcast", "musicvideo", "video")
    }
}
