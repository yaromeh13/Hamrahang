package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.LyricsEntry
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.ImageResolver

data class Video(
    val id: String,
    override val name: String?,
    override val names: Map<String, String> = emptyMap(),
    val url: String? = null,
    val artists: List<Artist> = emptyList(),
    val songs: List<String> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList(),
    val lyrics: List<LyricsEntry> = emptyList()
) : LocalizedTitle {
    val isMusicVideo: Boolean get() = meta?.type == "musicvideo"
    val contentType: ContentType get() = if (isMusicVideo) ContentType.MUSICVIDEO else ContentType.VIDEO
    val thumb: String get() = if (isMusicVideo) ImageResolver.musicVideo(meta) else ImageResolver.video(meta)
    val heroBackground: String? get() = thumb
}
