package ir.hamrahang.tv.playback

import ir.hamrahang.tv.domain.artist.Artist

data class PlaybackItem(
    val id: String,
    val title: String,
    val artwork: Any?,
    val artists: List<Artist>
)