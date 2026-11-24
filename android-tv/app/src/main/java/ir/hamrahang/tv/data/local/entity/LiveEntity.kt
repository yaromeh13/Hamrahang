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
    val updatedAt: Long
)
