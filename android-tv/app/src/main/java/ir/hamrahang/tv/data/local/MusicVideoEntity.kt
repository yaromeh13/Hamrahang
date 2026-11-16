package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "musicvideos")
data class MusicVideoEntity(

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
    val sources: String,

    // ---------- Cache ----------
    val isFull: Boolean,
    val updatedAt: Long

)