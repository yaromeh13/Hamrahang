package ir.hamrahang.tv.favorite

import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.FavoriteRepository
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.domain.video.Video

object FavoriteManager {

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

    }

    suspend fun toggle(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ): Boolean {

        return repository.toggle(
            mediaId = mediaId,
            type = type,
            userId = userId
        )

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