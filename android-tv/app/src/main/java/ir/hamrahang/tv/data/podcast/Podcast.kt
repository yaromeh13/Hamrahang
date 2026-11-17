package ir.hamrahang.tv.domain.podcast

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.ImageResolver

data class Podcast(
    val id: String,
    override val name: String?,
    override val names: Map<String, String> = emptyMap(),
    val url: String? = null,
    val artists: List<Artist> = emptyList(),
    val parents: List<ParentMini> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList()
) : LocalizedTitle {
    val thumb: String get() = ImageResolver.podcast(meta)
    val heroBackground: String? get() = ImageResolver.hero(meta)
}
