package ir.hamrahang.tv.data.playlist

import android.content.Context
import ir.hamrahang.tv.data.auth.AuthSessionStore
import ir.hamrahang.tv.data.favorite.UacAction
import ir.hamrahang.tv.data.favorite.UacRequest
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.dao.PlaylistDao
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.local.entity.PlaylistItemEntity
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.playback.PlaybackItem
import java.util.UUID

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
        dao.updateItemCount(playlist.localId, dao.countItems(playlist.localId), System.currentTimeMillis())

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
            } else if (response.error == "item_limit") {
                dao.deleteItem(playlist.localId, type, publicId.toString())
                dao.updateItemCount(playlist.localId, dao.countItems(playlist.localId), System.currentTimeMillis())
                throw IllegalStateException("item_limit")
            }
        } catch (error: IllegalStateException) {
            throw error
        } catch (_: Exception) {
            // Keep the local item and retry it after connectivity returns.
        }
        return true
    }

    suspend fun refreshAndSync(): Boolean {
        val owner = ownerId()
        val accessToken = token() ?: return false
        val authorization = "Bearer $accessToken"

        return try {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = authorization,
                body = UacRequest(UacAction(a = "get_playlists"))
            )
            if (response.error != null || response.playlists == null) return false

            response.playlists.forEach { dto ->
                val remoteId = dto.id?.toString() ?: return@forEach
                val existing = dao.getByRemoteId(remoteId, owner)
                dao.upsert(
                    (existing ?: PlaylistEntity(
                        localId = UUID.randomUUID().toString(),
                        remoteId = remoteId,
                        ownerUserId = owner,
                        name = dto.name.orEmpty(),
                        pendingSync = false
                    )).copy(
                        remoteId = remoteId,
                        name = dto.name?.takeIf { it.isNotBlank() } ?: existing?.name.orEmpty(),
                        thumbnail = dto.meta?.tmb ?: existing?.thumbnail,
                        itemCount = dto.meta?.nci ?: existing?.itemCount ?: 0,
                        isPublic = dto.isPublic ?: existing?.isPublic ?: false,
                        pendingSync = false,
                        updatedAt = System.currentTimeMillis()
                    )
                )
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
                } else if (response.error == "item_limit") {
                    // Keep the local item rather than silently discarding offline data.
                    return@itemLoop
                }
            }
            dao.updateItemCount(playlist.localId, dao.countItems(playlist.localId), System.currentTimeMillis())
        }
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
        private val PLAYABLE_TYPES = setOf("song", "podcast", "musicvideo", "video")
    }
}
