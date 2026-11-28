package ir.hamrahang.tv.data.local.entity

import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.dao.FavoriteDao

class FavoriteRepository(
    private val dao: FavoriteDao
) {

    suspend fun add(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ) {

        val typeValue = type.value

        if (
            dao.find(
                mediaId = mediaId,
                type = typeValue,
                userId = userId
            ) == null
        ) {

            dao.insert(

                FavoriteEntity(
                    mediaId = mediaId,
                    type = typeValue,
                    userId = userId
                )

            )

        }

    }

    suspend fun remove(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ) {

        dao.delete(
            mediaId = mediaId,
            type = type.value,
            userId = userId
        )

    }

    suspend fun toggle(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ): Boolean {

        val typeValue = type.value

        return if (
            dao.isFavorite(
                mediaId = mediaId,
                type = typeValue,
                userId = userId
            )
        ) {

            dao.delete(
                mediaId = mediaId,
                type = typeValue,
                userId = userId
            )

            false

        } else {

            dao.insert(

                FavoriteEntity(
                    mediaId = mediaId,
                    type = typeValue,
                    userId = userId
                )

            )

            true
        }

    }

    suspend fun isFavorite(
        mediaId: Long,
        type: ContentType,
        userId: Long = 0
    ): Boolean {

        return dao.isFavorite(
            mediaId = mediaId,
            type = type.value,
            userId = userId
        )

    }

    fun observeFavorites(
        type: ContentType,
        userId: Long = 0
    ) =
        dao.observeFavorites(
            type = type.value,
            userId = userId
        )

    suspend fun getFavorites(
        type: ContentType,
        userId: Long = 0
    ) =
        dao.getFavorites(
            type = type.value,
            userId = userId
        )

    suspend fun clear(
        userId: Long = 0
    ) {

        dao.clear(userId)

    }

    suspend fun clearAll() {

        dao.clearAll()

    }

}