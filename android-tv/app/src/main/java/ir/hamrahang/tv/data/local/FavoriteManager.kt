package ir.hamrahang.tv.favorite

import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.FavoriteRepository
import ir.hamrahang.tv.data.favorite.UacAction
import ir.hamrahang.tv.data.favorite.UacRequest
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.domain.video.Video

object FavoriteManager {

    @Volatile
    private var accessToken: String? = null

    fun setAccessToken(token: String?) {
        accessToken = token?.takeIf { it.isNotBlank() }
    }

    private suspend fun sendServerAction(
        action: String,
        mediaId: Long,
        type: ContentType
    ) {
        val token = accessToken ?: return
        runCatching {
            val response = ApiClient.api.favoriteAccountAction(
                authorization = "Bearer $token",
                body = UacRequest(
                    UacAction(
                        a = action,
                        p = type.apiKey,
                        q = listOf(mediaId.toString())
                    )
                )
            )
            if (response.error != null) {
                android.util.Log.w("FavoriteManager", "Favorite API failed: ${response.error}")
            }
        }.onFailure {
            android.util.Log.w("FavoriteManager", "Favorite API request failed", it)
        }
    }

    private val repository by lazy {

        FavoriteRepository(
            DatabaseProvider
                .database()
                .favoriteDao()
        )

    }

    suspend fun add(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ) {

        repository.add(
            mediaId = mediaId,
            type = type,
            userId = userId
        )
        sendServerAction("fav_add", mediaId, type)

    }

    suspend fun remove(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ) {

        repository.remove(
            mediaId = mediaId,
            type = type,
            userId = userId
        )
        sendServerAction("fav_remove", mediaId, type)

    }

    suspend fun toggle(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ): Boolean {

        val added = repository.toggle(
            mediaId = mediaId,
            type = type,
            userId = userId
        )
        sendServerAction(if (added) "fav_add" else "fav_remove", mediaId, type)
        return added

    }

    suspend fun isFavorite(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ): Boolean {

        return repository.isFavorite(
            mediaId = mediaId,
            type = type,
            userId = userId
        )

    }

    fun observeFavorites(
        type: ContentType,
        userId: Long = 0
    ) =
        repository.observeFavorites(
            type = type,
            userId = userId
        )

    suspend fun getFavorites(
        type: ContentType,
        userId: Long = 0
    ) =
        repository.getFavorites(
            type = type,
            userId = userId
        )

    suspend fun clear(
        userId: Long = 0
    ) {

        repository.clear(userId)

    }

    suspend fun clearAll() {

        repository.clearAll()

    }

    suspend fun syncWithServer(
        accessToken: String,
        userId: Long = 0
    ): Result<Int> {
        setAccessToken(accessToken)
        val syncRepository = ir.hamrahang.tv.data.favorite.FavoriteSyncRepository(
            DatabaseProvider.database().favoriteDao()
        )
        return syncRepository.pullAndSync(accessToken, userId)
    }

    fun contentTypeOf(video: Video): ContentType = video.contentType

    fun contentTypeOf(media: PlayerMedia): ContentType =
        media.contentType ?: contentTypeOf(media.type)

    fun contentTypeOf(
        mediaType: MediaType
    ): ContentType {
        return when (mediaType) {
            MediaType.AUDIO -> ContentType.SONG
            MediaType.VIDEO -> ContentType.VIDEO
        }
    }

}