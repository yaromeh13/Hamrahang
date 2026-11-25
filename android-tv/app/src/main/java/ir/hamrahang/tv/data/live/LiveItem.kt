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
    val sources: List<MediaSource>,
    val url: String? = null,
    val genres: List<LiveGenre> = emptyList(),
    val languages: List<String> = emptyList(),
    val owner: SourceOwner? = null
) : LocalizedTitle {
    val thumb: String
        get() = ImageResolver.thumb(meta?.tmb)
}
