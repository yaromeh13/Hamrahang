package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "songs")
data class SongEntity(
    @PrimaryKey val id: String,
    val name: String?,
    val names: String,
    val artistIds: String,
    val parentIds: String,
    val meta: String,
    val sources: String,
    val isFull: Boolean,
    val updatedAt: Long
)
