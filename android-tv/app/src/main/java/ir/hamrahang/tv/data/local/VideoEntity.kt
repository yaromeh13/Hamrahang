package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "videos")
data class VideoEntity(
    @PrimaryKey val id: String,
    val name: String?,
    val names: String,
    val artistIds: String,
    val songIds: String,
    val meta: String,
    val sources: String,
    val isFull: Boolean,
    val updatedAt: Long
)
