package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.FavoriteEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface FavoriteDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(entity: FavoriteEntity)

    @Query(
        """
        SELECT *
        FROM favorites
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
        LIMIT 1
        """
    )
    suspend fun find(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    ): FavoriteEntity?

    @Query(
        """
    SELECT EXISTS(
        SELECT 1
        FROM favorites
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
        AND deleted = 0
    )
    """
    )
    suspend fun isFavorite(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    ): Boolean

    @Query(
        """
        DELETE FROM favorites
        WHERE mediaId = :mediaId
        AND type = :type
        AND userId = :userId
        """
    )
    suspend fun delete(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    )

    @Query("""
    UPDATE favorites
    SET deleted = 1,
        synced = 0
    WHERE mediaId = :mediaId
    AND type = :type
    AND userId = :userId
    """)
    suspend fun markDeleted(
        mediaId: Long,
        type: Int,
        userId: Long = 0
    )

    @Query(
        """
        SELECT *
        FROM favorites
        WHERE type = :type
        AND userId = :userId
        AND deleted = 0
        ORDER BY createdAt DESC
        """
    )
    fun observeFavorites(
        type: Int,
        userId: Long = 0
    ): Flow<List<FavoriteEntity>>

    @Query(
        """
        SELECT *
        FROM favorites
        WHERE type = :type
        AND userId = :userId
        AND deleted = 0
        ORDER BY createdAt DESC
        """
    )
    suspend fun getFavorites(
        type: Int,
        userId: Long = 0
    ): List<FavoriteEntity>

    @Query(
        """
        DELETE FROM favorites
        WHERE userId = :userId
        """
    )
    suspend fun clear(
        userId: Long = 0
    )

    @Query("DELETE FROM favorites")
    suspend fun clearAll()
}