package ir.hamrahang.tv.ui

import ir.hamrahang.tv.data.local.ContentType

object UiActionDispatcher {

    fun dispatch(
        navigator: UiNavigator,
        event: UiClickEvent
    ) {
        when (event) {
            is UiClickEvent.MusicVideoClick ->
                navigator.openMediaDetail(ContentType.MUSICVIDEO, event.video.id, event.session)

            is UiClickEvent.VideoClick ->
                navigator.openMediaDetail(ContentType.VIDEO, event.video.id, event.session)

            is UiClickEvent.SongClick ->
                navigator.openMediaDetail(ContentType.SONG, event.song.id, event.session)

            is UiClickEvent.ArtistClick ->
                navigator.openArtist(event.artist)

            is UiClickEvent.AlbumClick ->
                navigator.openMediaDetail(ContentType.ALBUM, event.album.id)

            is UiClickEvent.PodcastClick ->
                navigator.openMediaDetail(ContentType.PODCAST, event.podcast.id, event.session)

            is UiClickEvent.PlaySession ->
                navigator.openPlayback()
        }
    }
}
