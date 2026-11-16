package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "podcasts")
data class PodcastEntity(

    @PrimaryKey
    val id: String,

    val title: String?,
    val titleFa: String?,

    val artistIds: String,
    val parentIds: String,

    val meta: String,
    val sources: String,

    val isFull: Boolean,
    val updatedAt: Long

)