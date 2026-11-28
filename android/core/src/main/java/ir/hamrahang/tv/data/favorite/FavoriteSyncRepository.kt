package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.dao.FavoriteDao
import ir.hamrahang.tv.data.local.entity.FavoriteEntity
import ir.hamrahang.tv.network.ApiClient

/**
 * Add-only Favorite synchronization, following index.html:
 * 1) pull server favorites
 * 2) merge server items into Room without deleting local items
 * 3) push the merged local set using sync_favorites
 * 4) merge the server response again
 *
 * A token is supplied by the authenticated account/session layer.
 */
class FavoriteSyncRepository(
    private val dao: FavoriteDao
) {
    suspend fun pullAndSync(
        accessToken: String,
        userId: Long = 0
    ): Result<Int> = runCatching {
        require(accessToken.isNotBlank()) { "Missing access token" }

        val authorization = "Bearer $accessToken"
        val pulled = ApiClient.api.favoriteAccountAction(
            authorization = authorization,
            body = UacRequest(UacAction(a = "get_favorites"))
        )
        check(pulled.error == null) { pulled.error ?: "Favorite fetch failed" }

        val addedFromServer = mergeIntoLocal(pulled.favorites.orEmpty(), userId)

        val localItems = dao.getAllFavorites(userId)
            .groupBy { it.type }
            .mapNotNull { (typeValue, rows) ->
                val type = ContentType.values().firstOrNull { it.value == typeValue }
                    ?: return@mapNotNull null
                type.apiKey to rows.map { it.mediaId.toString() }.distinct()
            }
            .toMap()

        val pushed = ApiClient.api.favoriteAccountAction(
            authorization = authorization,
            body = UacRequest(
                UacAction(
                    a = "sync_favorites",
                    items = localItems
                )
            )
        )
        check(pushed.error == null) { pushed.error ?: "Favorite sync failed" }

        mergeIntoLocal(pushed.favorites.orEmpty(), userId) + addedFromServer
    }

    private suspend fun mergeIntoLocal(
        serverFavorites: Map<String, List<String>>,
        userId: Long
    ): Int {
        var added = 0
        serverFavorites.forEach { (typeName, ids) ->
            val type = ContentType.values().firstOrNull { it.apiKey == typeName }
                ?: return@forEach
            ids.forEach idLoop@{ publicId ->
                val mediaId = publicId.toLongOrNull() ?: return@idLoop
                val existing = dao.find(mediaId, type.value, userId)
                if (existing == null) {
                    dao.insert(
                        FavoriteEntity(
                            mediaId = mediaId,
                            type = type.value,
                            userId = userId,
                            remoteId = publicId,
                            synced = true
                        )
                    )
                    added++
                }
            }
        }
        return added
    }
}
