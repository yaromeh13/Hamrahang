package ir.hamrahang.tv.data.local.playback

import ir.hamrahang.tv.data.local.dao.PlaybackHistoryDao
import kotlinx.coroutines.flow.Flow

class PlaybackHistoryRepository(
    private val dao: PlaybackHistoryDao
) {

    suspend fun record(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    ) {

        val now =
            System.currentTimeMillis()

        val existing =
            dao.find(
                mediaId = mediaId,
                type = type,
                userId = userId
            )

        if (existing == null) {

            dao.insert(
                PlaybackHistoryEntity(
                    mediaId = mediaId,
                    type = type,
                    userId = userId,
                    lastPlayed = now
                )
            )

        } else {

            dao.updateLastPlayed(
                mediaId = mediaId,
                type = type,
                userId = userId,
                lastPlayed = now
            )
        }
    }

    //==================================================
    // Reactive - برای Home
    //==================================================

    fun observeLastPlayed(
        type: Int,
        userId: Long = 0,
        limit: Int = 30
    ): Flow<List<PlaybackHistoryEntity>> {

        return dao.observeLastPlayed(
            type = type,
            userId = userId,
            limit = limit
        )
    }

    //==================================================
    // One Shot - برای مواقعی که فقط یک بار داده لازم داریم
    //==================================================

    suspend fun getLastPlayed(
        type: Int,
        userId: Long = 0,
        limit: Int = 30
    ): List<PlaybackHistoryEntity> {

        return dao.getLastPlayed(
            type = type,
            userId = userId,
            limit = limit
        )
    }

    //==================================================

    suspend fun delete(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    ) {

        dao.delete(
            mediaId = mediaId,
            type = type,
            userId = userId
        )
    }

    suspend fun clear(
        userId: Long = 0
    ) {

        dao.clear(userId)
    }

    suspend fun clearAll() {

        dao.clearAll()
    }
}