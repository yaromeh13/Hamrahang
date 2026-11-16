package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface PlaybackHistoryDao {

    @Insert(onConflict = OnConflictStrategy.IGNORE)
    suspend fun insert(
        item: PlaybackHistoryEntity
    )

    @Query("""
        UPDATE playback_history
        SET lastPlayed = :lastPlayed
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
    """)
    suspend fun updateLastPlayed(
        mediaId: Long,
        type: Int,
        userId: Long,
        lastPlayed: Long
    )

    @Query("""
        SELECT *
        FROM playback_history
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
        LIMIT 1
    """)
    suspend fun find(
        mediaId: Long,
        type: Int,
        userId: Long
    ): PlaybackHistoryEntity?

    @Query("""
        SELECT *
        FROM playback_history
        WHERE type = :type
        AND userId = :userId
        ORDER BY lastPlayed DESC
        LIMIT :limit
    """)
    fun observeLastPlayed(
        type: Int,
        userId: Long = 0,
        limit: Int = 30
    ): Flow<List<PlaybackHistoryEntity>>

    @Query("""
        SELECT *
        FROM playback_history
        WHERE type = :type
        AND userId = :userId
        ORDER BY lastPlayed DESC
        LIMIT :limit
    """)
    suspend fun getLastPlayed(
        type: Int,
        userId: Long = 0,
        limit: Int = 30
    ): List<PlaybackHistoryEntity>

    @Query("""
        DELETE FROM playback_history
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
    """)
    suspend fun delete(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    )

    @Query("""
        DELETE FROM playback_history
        WHERE userId = :userId
    """)
    suspend fun clear(
        userId: Long = 0
    )

    @Query("""
        DELETE FROM playback_history
    """)
    suspend fun clearAll()
}