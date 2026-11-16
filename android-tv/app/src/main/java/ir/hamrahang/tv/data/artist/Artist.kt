package ir.hamrahang.tv.domain.artist

import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.util.ImageResolver

data class Artist(
    val id: String,
    override val title: String? = null,
    override val titleFa: String? = null,
    //val url: String? = null,
    val meta: ArtistMeta? = null,
    val albumIds: List<String> = emptyList(),
    val songIds: List<String> = emptyList(),
    val musicVideoIds: List<String> = emptyList(),
    val videoIds: List<String> = emptyList(),
    val podcastIds: List<String> = emptyList()
): LocalizedTitle{
    val thumb:String
        get() = ImageResolver.thumb(meta?.tmb)
}