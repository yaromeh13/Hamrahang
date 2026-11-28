package ir.hamrahang.tv.playback

import ir.hamrahang.tv.player.MediaType

data class PlaybackSession(
    val id: String,
    val title: String,
    val source: PlaybackSource,
    val items: List<PlaybackItem>,
    val currentIndex: Int,
    val mediaType: MediaType
)