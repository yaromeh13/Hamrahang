package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.MusicVideoEntity

@Dao
interface MusicVideoDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(item: MusicVideoEntity)

    @Query("SELECT * FROM musicvideos")
    suspend fun getAll(): List<MusicVideoEntity>

    @Query("SELECT * FROM musicvideos WHERE id = :id LIMIT 1")
    suspend fun get(id: String): MusicVideoEntity?

    @Query("SELECT * FROM musicvideos WHERE id IN (:ids)")
    suspend fun getByIds(ids: List<String>): List<MusicVideoEntity>
}
