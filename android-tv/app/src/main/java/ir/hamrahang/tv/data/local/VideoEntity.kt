package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "videos")
data class VideoEntity(

    @PrimaryKey
    val id: Long,

    val title: String,

    val titleFa: String,

    val artistId: Long,

    val artistName: String,

    val thumb: String?,

    val duration: Long,

    val source720: String?,

    val source1080: String?,

    val source4k: String?,

    val updatedAt: Long =
        System.currentTimeMillis()
)