package ir.hamrahang.tv.data.musicvideo

data class VideoDto(
    val id: String?,
    val name: String?,
    val name_fa: String?,
    val artist: String?,
    val artist_fa: String?,
    val thumb: String?,
    val video: List<VideoSource>?
)

data class VideoSource(
    val url: String,
    val bit: String?
)
