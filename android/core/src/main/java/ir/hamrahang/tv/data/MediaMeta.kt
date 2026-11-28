package ir.hamrahang.tv.data

import com.google.gson.JsonElement

data class MediaMeta(
    val type: String? = null,
    val act: Int? = null,
    val tmb: String? = null,
    val art: String? = null,
    // The web API may return lyric references as either a string or an array.
    val lrc: JsonElement? = null,
    val des: String? = null,
    val gnr: List<Int>? = null,
    val lan: String? = null,
    val rls: String? = null,
    val lik: Long? = null,
    val ncp: Long? = null,
    val dur: Long? = null,
    val tmv: String? = null,
    val cvr: String? = null,
    val gal: List<String>? = null
)
