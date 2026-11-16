package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.ImageResolver

data class Video(
    val id: String,
    override val title: String?,
    override val titleFa: String? = null,
    val url: String? = null,
    val artists: List<Artist> = emptyList(),
    val songs: List<String> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList()
) : LocalizedTitle {
    val isMusicVideo: Boolean
        get() = meta?.type == "musicvideo"

    val contentType: ir.hamrahang.tv.data.local.ContentType
        get() = if (isMusicVideo) {
            ir.hamrahang.tv.data.local.ContentType.MUSICVIDEO
        } else {
            ir.hamrahang.tv.data.local.ContentType.VIDEO
        }

    val thumb: String
        get() = ImageResolver.thumb(meta?.tmb)
}
