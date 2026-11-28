package ir.hamrahang.tv.data.local.entity

import androidx.room.ColumnInfo
import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "musicvideos")
data class MusicVideoEntity(
    @PrimaryKey val id: String,
    val name: String?,
    val names: String,
    val artistIds: String,
    val songIds: String,
    val meta: String,
    val sources: String,
    @ColumnInfo(defaultValue = "'[]'") val lyrics: String = "[]",
    val isFull: Boolean,
    val updatedAt: Long
)
