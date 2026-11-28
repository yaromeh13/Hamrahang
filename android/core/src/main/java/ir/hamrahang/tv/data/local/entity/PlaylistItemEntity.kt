package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index

@Entity(
    tableName = "playlist_items",
    primaryKeys = ["localPlaylistId", "itemType", "itemId"],
    foreignKeys = [
        ForeignKey(
            entity = PlaylistEntity::class,
            parentColumns = ["localId"],
            childColumns = ["localPlaylistId"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index(value = ["localPlaylistId"])]
)
data class PlaylistItemEntity(
    val localPlaylistId: String,
    val itemType: String,
    val itemId: String,
    val position: Int,
    val addedAt: Long = System.currentTimeMillis(),
    val synced: Boolean = false
)
