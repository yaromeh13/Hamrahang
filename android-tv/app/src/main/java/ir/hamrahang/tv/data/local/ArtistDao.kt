package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.ArtistEntity

@Dao
interface ArtistDao {

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(
        artist: ArtistEntity
    )

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertAll(
        list: List<ArtistEntity>
    )

    @Query("SELECT * FROM artists")
    suspend fun getAll(): List<ArtistEntity>

    @Query("SELECT * FROM artists WHERE id=:id")
    suspend fun get(
        id: String
    ): ArtistEntity?

    @Query("SELECT * FROM artists WHERE id IN (:ids)")
    suspend fun getByIds(
        ids: List<String>
    ): List<ArtistEntity>
}