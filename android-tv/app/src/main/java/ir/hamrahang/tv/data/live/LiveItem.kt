package ir.hamrahang.tv.data.live

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.util.ImageResolver

data class LiveItem(
    val id: String,
    override val name: String?,
    override val names: Map<String, String>?,
    val meta: MediaMeta?,
    val sources: List<MediaSource>
) : LocalizedTitle {
    val thumb: String
        get() = ImageResolver.thumb(meta?.tmb)

    val owner: SourceOwner?
        get() = sources.firstNotNullOfOrNull { it.owner }
}
