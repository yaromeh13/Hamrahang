package ir.hamrahang.tv.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.local.entity.PlaylistItemEntity

@Dao
interface PlaylistDao {
    @Query("SELECT * FROM playlists WHERE ownerUserId = :ownerUserId ORDER BY updatedAt DESC, name COLLATE NOCASE ASC")
    suspend fun getPlaylists(ownerUserId: String): List<PlaylistEntity>

    @Query("SELECT * FROM playlists WHERE ownerUserId = :ownerUserId AND pendingSync = 1 AND remoteId IS NULL ORDER BY updatedAt ASC")
    suspend fun getPendingPlaylists(ownerUserId: String): List<PlaylistEntity>

    @Query("SELECT * FROM playlists WHERE localId = :localId LIMIT 1")
    suspend fun getPlaylist(localId: String): PlaylistEntity?

    @Query("SELECT * FROM playlists WHERE remoteId = :remoteId AND ownerUserId = :ownerUserId LIMIT 1")
    suspend fun getByRemoteId(remoteId: String, ownerUserId: String): PlaylistEntity?

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsert(playlist: PlaylistEntity)

    @Query("DELETE FROM playlists WHERE localId = :localId")
    suspend fun deletePlaylist(localId: String)

    @Insert(onConflict = OnConflictStrategy.IGNORE)
    suspend fun insertItem(item: PlaylistItemEntity): Long

    @Query("SELECT * FROM playlist_items WHERE localPlaylistId = :localId ORDER BY position ASC")
    suspend fun getItems(localId: String): List<PlaylistItemEntity>

    @Query("SELECT * FROM playlist_items WHERE localPlaylistId = :localId AND synced = 0 ORDER BY position ASC")
    suspend fun getUnsyncedItems(localId: String): List<PlaylistItemEntity>

    @Query("UPDATE playlist_items SET synced = 1 WHERE localPlaylistId = :localId AND itemType = :itemType AND itemId = :itemId")
    suspend fun markItemSynced(localId: String, itemType: String, itemId: String)

    @Query("UPDATE playlist_items SET synced = 1 WHERE localPlaylistId = :localId")
    suspend fun markAllItemsSynced(localId: String)

    @Query("UPDATE playlist_items SET synced = 1 WHERE localPlaylistId = :localId AND position < :count")
    suspend fun markItemsSyncedBefore(localId: String, count: Int)

    @Query("DELETE FROM playlist_items WHERE localPlaylistId = :localId AND itemType = :itemType AND itemId = :itemId")
    suspend fun deleteItem(localId: String, itemType: String, itemId: String)

    @Query("SELECT COUNT(*) FROM playlist_items WHERE localPlaylistId = :localId")
    suspend fun countItems(localId: String): Int

    @Query("UPDATE playlists SET itemCount = :count, updatedAt = :updatedAt WHERE localId = :localId")
    suspend fun updateItemCount(localId: String, count: Int, updatedAt: Long)
}
