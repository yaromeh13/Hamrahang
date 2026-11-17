package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "artists")
data class ArtistEntity(
    @PrimaryKey val id: String,
    val name: String?,
    val names: String,
    val meta: String,
    val albumIds: String,
    val songIds: String,
    val musicvideoIds: String,
    val videoIds: String,
    val podcastIds: String,
    val isFull: Boolean,
    val updatedAt: Long
)
