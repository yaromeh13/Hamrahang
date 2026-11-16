package ir.hamrahang.tv.player

data class PlayerMetadata(

    val release: String? = null,
    val playCount: Long? = null,
    val albumName: String? = null,
    val genres: List<String> = emptyList(),
    val language: String? = null,
    val year: String? = null

)