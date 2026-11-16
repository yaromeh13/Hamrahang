package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.AlbumEntity

@Dao
interface AlbumDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(
        album: AlbumEntity
    )

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertAll(
        list: List<AlbumEntity>
    )

    @Query("SELECT * FROM albums")
    suspend fun getAll(): List<AlbumEntity>

    @Query("SELECT * FROM albums WHERE id=:id")
    suspend fun get(
        id: String
    ): AlbumEntity?

    @Query("SELECT * FROM albums WHERE id IN (:ids)")
    suspend fun getByIds(
        ids: List<String>
    ): List<AlbumEntity>

}