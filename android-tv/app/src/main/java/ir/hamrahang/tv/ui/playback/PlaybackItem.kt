package ir.hamrahang.tv.playback

import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.data.local.ContentType

data class PlaybackItem(
    val id: String,
    val title: String,
    val artwork: Any?,
    val artists: List<Artist>,
    val contentType: ContentType = ContentType.SONG
)