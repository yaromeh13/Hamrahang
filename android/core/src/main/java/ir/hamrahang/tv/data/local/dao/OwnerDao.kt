package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.OwnerEntity

@Dao
interface OwnerDao {

    // Match the web cache: an existing owner is not overwritten by a later source.
    @Insert(onConflict = OnConflictStrategy.IGNORE)
    suspend fun insertIfAbsent(owner: OwnerEntity): Long

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsert(owner: OwnerEntity)

    @Query("SELECT * FROM owners WHERE id = :id LIMIT 1")
    suspend fun get(id: Long): OwnerEntity?

    @Query("SELECT * FROM owners WHERE id IN (:ids)")
    suspend fun getByIds(ids: List<Long>): List<OwnerEntity>

    @Query("SELECT * FROM owners")
    suspend fun getAll(): List<OwnerEntity>

}
