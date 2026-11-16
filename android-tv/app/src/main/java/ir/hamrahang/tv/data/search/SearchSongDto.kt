package ir.hamrahang.tv.data.search

data class SearchSongDto(
    val name: String?,
    val nameFa: String?,
    val artist: String?,
    val artistFa: String?,
    val artistUrl: String?,
    val thumb: String?,
    val audio: List<SongSource>?
)

data class SongSource(
    val url: String,
    val bit: String?
)