package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "albums")
data class AlbumEntity(

    @PrimaryKey
    val id: String,

    // ---------- Titles ----------
    val title: String?,
    val titleFa: String?,

    // ---------- Basic ----------
    //val url: String?,

    // ---------- Relations ----------
    val artistIds: String,
    val songIds: String,

    // ---------- Full JSON ----------
    val meta: String,

    // ---------- Cache ----------
    val isFull: Boolean,
    val updatedAt: Long

)