package ir.hamrahang.tv.data.local.entity

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
    val genres: String = "[]",
    val languages: String = "[]",
    val owner: String? = null,
    val isFull: Boolean = false
)
