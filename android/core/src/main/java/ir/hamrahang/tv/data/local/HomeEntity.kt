package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "home")
data class HomeEntity(

    @PrimaryKey
    val id: String,

    // JSON<List<String>>
    val itemIds: String,

    val updatedAt: Long

)