package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "playlists",
    indices = [
        Index(value = ["remoteId"]),
        Index(value = ["ownerUserId"])
    ]
)
data class PlaylistEntity(
    @PrimaryKey
    val localId: String,
    val remoteId: String? = null,
    val ownerUserId: String = "",
    val name: String,
    val thumbnail: String? = null,
    val itemCount: Int = 0,
    val isPublic: Boolean = false,
    val pendingSync: Boolean = true,
    val updatedAt: Long = System.currentTimeMillis()
)
