package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.util.ImageResolver

data class Album(
    val id: String,
    override val name: String? = null,
    override val names: Map<String, String> = emptyMap(),
    val artists: List<String> = emptyList(),
    val songs: List<String> = emptyList(),
    val meta: MediaMeta? = null
) : LocalizedTitle {
    val thumb: String get() = ImageResolver.album(meta)
    val heroBackground: String get() = thumb
}
