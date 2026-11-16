package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "musicvideos")
data class MusicVideoEntity(
    @PrimaryKey
    val id: String,
    val title: String?,
    val titleFa: String?,
    val artistIds: String,
    val songIds: String,
    val meta: String,
    val sources: String,
    val isFull: Boolean,
    val updatedAt: Long
)
