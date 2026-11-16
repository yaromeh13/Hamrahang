package ir.hamrahang.tv.data

data class MediaSource(

    val url: String,
    val refId: String? = null,
    val bit: String? = null,
    val type: String? = null,
    val mime: String? = null,
    val size: Long? = null

)