package ir.hamrahang.tv.data.local.entity

import androidx.room.ColumnInfo
import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "live")
data class LiveEntity(
    @PrimaryKey
    val id: String,
    val name: String?,
    val names: String,
    val meta: String,
    val sources: String,
    val position: Int,
    val updatedAt: Long,
    val url: String? = null,
    @ColumnInfo(defaultValue = "'[]'")
    val genres: String = "[]",
    @ColumnInfo(defaultValue = "'[]'")
    val languages: String = "[]",
    val owner: String? = null,
    @ColumnInfo(defaultValue = "0")
    val isFull: Boolean = false,
    @ColumnInfo(defaultValue = "'audio'")
    val mediaType: String = "audio"
)
