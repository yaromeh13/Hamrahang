package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.util.ImageResolver

data class Album(
    val id: String,
    override val title: String? = null,
    override val titleFa: String? = null,
    val artists: List<String> = emptyList(),
    val songs: List<String> = emptyList(),
    val meta: MediaMeta? = null
) : LocalizedTitle {
    val thumb: String
        get() = ImageResolver.album(meta)

    val heroBackground: String?
        get() = ImageResolver.hero(meta)
}
