package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "artists")
data class ArtistEntity(

    @PrimaryKey
    val id: String,

    // ---------- Titles ----------
    val title: String?,
    val titleFa: String?,

    // ---------- Basic ----------
    //val url: String?,

    // ---------- Full JSON ----------
    val meta: String,

    // ---------- Relations ----------
    val albumIds: String,
    val songIds: String,
    val musicvideoIds: String,
    val videoIds: String,
    val podcastIds: String,

    // ---------- Cache ----------
    val isFull: Boolean,
    val updatedAt: Long

)