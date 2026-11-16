package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import ir.hamrahang.tv.data.local.entity.UserEntity

@Dao
interface UserDao {

    @Insert(
        onConflict = OnConflictStrategy.REPLACE
    )
    suspend fun insert(
        user: UserEntity
    ): Long

    @Update
    suspend fun update(
        user: UserEntity
    )

    @Query(
        "SELECT * FROM users WHERE id = :id LIMIT 1"
    )
    suspend fun get(
        id: Long
    ): UserEntity?

    @Query(
        "SELECT * FROM users WHERE remoteId = :remoteId LIMIT 1"
    )
    suspend fun getByRemoteId(
        remoteId: String
    ): UserEntity?

    @Query(
        "SELECT * FROM users WHERE isLoggedIn = 1 LIMIT 1"
    )
    suspend fun getLoggedInUser(): UserEntity?

    @Query(
        "UPDATE users SET isLoggedIn = 0"
    )
    suspend fun logoutAll()

    @Query(
        "DELETE FROM users WHERE id = :id"
    )
    suspend fun delete(
        id: Long
    )

    @Query(
        "DELETE FROM users"
    )
    suspend fun clearAll()
}