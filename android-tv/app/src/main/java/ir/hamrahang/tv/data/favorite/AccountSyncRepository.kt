package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.data.auth.HistorySyncItem

import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.FavoriteEntity
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryEntity
import ir.hamrahang.tv.network.ApiClient

/**
 * Mirrors index.html: pull server data first, then offer an add-only local-to-account sync.
 */
class AccountSyncRepository {
    private val database get() = DatabaseProvider.database()

    suspend fun pullServerData(accessToken: String) {
        val authorization = "Bearer $accessToken"

        runCatching {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = authorization,
                body = UacRequest(UacAction(a = "get_favorites"))
            )
            if (response.error == null) mergeFavorites(response.favorites.orEmpty())
        }

        runCatching {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = authorization,
                body = UacRequest(UacAction(a = "get_history"))
            )
            if (response.error == null) mergeHistory(response.history.orEmpty())
        }
    }

    suspend fun hasLocalDataToSync(): Boolean {
        if (database.favoriteDao().getAllFavorites(0).isNotEmpty()) return true
        return playableTypes().any { type ->
            database.playbackHistoryDao().getLastPlayed(type.value, 0, 50).isNotEmpty()
        }
    }

    suspend fun syncLocalData(accessToken: String): Boolean {
        val favoritesResult = FavoriteSyncRepository(database.favoriteDao())
            .pullAndSync(accessToken, 0)

        val historyResult = runCatching {
            val items = playableTypes().flatMap { type ->
                database.playbackHistoryDao().getLastPlayed(type.value, 0, 50).mapNotNull { row ->
                    if (row.mediaId <= 0L) null
                    else HistorySyncItem(
                        id = row.mediaId,
                        type = type.apiKey,
                        at = (row.lastPlayed / 1000L).coerceAtLeast(1L)
                    )
                }
            }

            if (items.isNotEmpty()) {
                val response = ApiClient.api.favoriteAccountAction(
                    authorization = "Bearer $accessToken",
                    body = UacRequest(UacAction(a = "sync_history", items = items))
                )
                check(response.error == null) { response.error ?: "History sync failed" }
            }
        }

        return favoritesResult.isSuccess && historyResult.isSuccess
    }

    private suspend fun mergeFavorites(favorites: Map<String, List<String>>) {
        val dao = database.favoriteDao()
        favorites.forEach { (typeName, ids) ->
            val type = ContentType.values().firstOrNull { it.apiKey == typeName }
                ?: return@forEach
            ids.forEach idLoop@{ publicId ->
                val id = publicId.toLongOrNull() ?: return@idLoop
                if (dao.find(id, type.value, 0) == null) {
                    dao.insert(
                        FavoriteEntity(
                            mediaId = id,
                            type = type.value,
                            userId = 0,
                            remoteId = publicId,
                            synced = true
                        )
                    )
                }
            }
        }
    }

    private suspend fun mergeHistory(history: List<HistorySyncItem>) {
        val dao = database.playbackHistoryDao()
        history.forEach { item ->
            val type = ContentType.values().firstOrNull { it.apiKey == item.type }
                ?: return@forEach
            if (type !in playableTypes() || item.id <= 0L) return@forEach
            val timestamp = (item.at * 1000L).coerceAtLeast(1L)
            val existing = dao.find(item.id, type.value, 0)
            if (existing == null) {
                dao.insert(
                    PlaybackHistoryEntity(
                        mediaId = item.id,
                        type = type.value,
                        userId = 0,
                        lastPlayed = timestamp
                    )
                )
            } else if (timestamp > existing.lastPlayed) {
                dao.updateLastPlayed(item.id, type.value, 0, timestamp)
            }
        }
    }

    private fun playableTypes() = listOf(
        ContentType.SONG,
        ContentType.PODCAST,
        ContentType.MUSICVIDEO,
        ContentType.VIDEO
    )
}
