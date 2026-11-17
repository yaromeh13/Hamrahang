package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "owners")
data class OwnerEntity(
    @PrimaryKey
    val id: Long,
    val name: String,
    val meta: String,
    val updatedAt: Long
)
