package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "favorites",
    indices = [
        Index(
            value = ["mediaId", "type", "userId"],
            unique = true
        )
    ]
)
data class FavoriteEntity(

    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,

    val mediaId: Long,

    val type: Int,

    val userId: Long = 0,

    val createdAt: Long = System.currentTimeMillis(),

    // Sync

    val remoteId: String? = null,

    val synced: Boolean = false,

    val deleted: Boolean = false
)