package ir.hamrahang.tv.data

data class MediaMeta(

    val type: String? = null,

    val tmb: String?,
    val art: String?,
    val lrc: String?,
    val des: String?,
    val gnr: List<Int>?,
    val lan: String?,
    val rls: String?,
    val lik: Long?,
    val ncp: Long?

)