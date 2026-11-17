package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "albums")
data class AlbumEntity(
    @PrimaryKey val id: String,
    val name: String?,
    val names: String,
    val artistIds: String,
    val songIds: String,
    val meta: String,
    val isFull: Boolean,
    val updatedAt: Long
)
