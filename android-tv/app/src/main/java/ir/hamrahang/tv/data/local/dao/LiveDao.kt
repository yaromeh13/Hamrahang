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

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsertAll(items: List<LiveEntity>)

    @Query("DELETE FROM live WHERE id NOT IN (:ids)")
    suspend fun deleteExcept(ids: List<String>)

    @Query("DELETE FROM live")
    suspend fun clear()
}
