package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.HomeEntity

@Dao
interface HomeDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(
        home: HomeEntity
    )

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertAll(
        list: List<HomeEntity>
    )

    @Query("SELECT * FROM home")
    suspend fun getAll(): List<HomeEntity>

    @Query("SELECT * FROM home WHERE id = :id")
    suspend fun get(
        id: String
    ): HomeEntity?

    @Query("DELETE FROM home")
    suspend fun clear()

    @Query("DELETE FROM home WHERE id = :id")
    suspend fun delete(
        id: String
    )
}