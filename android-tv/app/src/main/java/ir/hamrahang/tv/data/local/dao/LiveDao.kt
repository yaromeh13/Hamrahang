package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.LiveEntity

@Dao
interface LiveDao {
    @Query("SELECT * FROM live ORDER BY position ASC, updatedAt DESC")
    suspend fun getAll(): List<LiveEntity>

    @Query("SELECT * FROM live WHERE id = :id LIMIT 1")
    suspend fun get(id: String): LiveEntity?

    @Query("SELECT * FROM live WHERE id IN (:ids)")
    suspend fun getByIds(ids: List<String>): List<LiveEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsertAll(items: List<LiveEntity>)

    @Query("DELETE FROM live WHERE id NOT IN (:ids)")
    suspend fun deleteExcept(ids: List<String>)

    @Query("DELETE FROM live")
    suspend fun clear()
}
