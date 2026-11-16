package ir.hamrahang.tv.data.local.playback

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "playback_history",
    indices = [
        Index(
            value = ["mediaId", "type", "userId"],
            unique = true
        ),
        Index("lastPlayed"),
        Index("type")
    ]
)
data class PlaybackHistoryEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val mediaId: Long,
    val type: Int,
    val userId: Long = 0,
    val lastPlayed: Long
)