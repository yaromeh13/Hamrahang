package ir.hamrahang.tv.ui

import android.util.Log

object UiActionDispatcher {

    fun dispatch(
        navigator: UiNavigator,
        event: UiClickEvent
    ) {

        when (event) {

            is UiClickEvent.MusicVideoClick -> {
                //navigator.openFullscreenPlayer()
                navigator.openVideo()
            }

            is UiClickEvent.VideoClick -> { Log.d("NAV", "VideoClick received") }

            is UiClickEvent.SongClick -> {
                navigator.openPlayback()
            }

            is UiClickEvent.ArtistClick -> {
                navigator.openArtist(event.artist)
            }

            is UiClickEvent.AlbumClick -> {
                navigator.openPlayback(event.album)
            }

            is UiClickEvent.PodcastClick -> {
                navigator.openPlayback()
            }

            is UiClickEvent.PlaySession -> {
                navigator.openPlayback()
            }
        }
    }
}