package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.VideoEntity

@Dao
interface VideoDao {

    @Insert(
        onConflict = OnConflictStrategy.REPLACE
    )
    suspend fun insertAll(
        items: List<VideoEntity>
    )

    @Query(
        "SELECT * FROM videos"
    )
    suspend fun getAll(): List<VideoEntity>

    @Query(
        "SELECT * FROM videos WHERE id = :id LIMIT 1"
    )
    suspend fun getById(
        id: Long
    ): VideoEntity?

    @Query("SELECT * FROM videos WHERE id IN (:ids)")
    suspend fun getByIds(
        ids: List<String>
    ): List<VideoEntity>
}