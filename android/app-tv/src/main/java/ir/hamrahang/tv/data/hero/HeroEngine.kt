package ir.hamrahang.tv.hero

import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

class HeroEngine {

    private val _state = MutableStateFlow<HeroState?>(null)
    val state: StateFlow<HeroState?> = _state

    private var lastKey: String? = null

    fun dispatch(event: HeroEvent) {

        val newState = when (event) {

            is HeroEvent.VideoFocused -> HeroState(
                style = HeroStyle.VIDEO,
                musicVideo = event.video
            )

            is HeroEvent.ArtistFocused -> HeroState(
                style = HeroStyle.ARTIST,
                artist = event.artist
            )

            is HeroEvent.AlbumFocused -> HeroState(
                style = HeroStyle.ALBUM,
                album = event.album
            )

            is HeroEvent.SongFocused -> HeroState(
                style = HeroStyle.SONG,
                song = event.song
            )

            is HeroEvent.PodcastFocused -> HeroState(
                style = HeroStyle.PODCAST,
                podcast = event.podcast
            )

            is HeroEvent.PlaylistFocused -> HeroState(
                style = HeroStyle.PLAYLIST,
                playlist = event.playlist
            )
        }

        val key = when (newState.style) {

            HeroStyle.ARTIST ->
                "artist:${newState.artist?.id}:${newState.artist?.meta.hashCode()}"

            HeroStyle.ALBUM ->
                "album:${newState.album?.id}:${newState.album?.meta.hashCode()}"

            HeroStyle.SONG ->
                "song:${newState.song?.id}:${newState.song?.sources.hashCode()}"

            HeroStyle.PODCAST ->
                "podcast:${newState.podcast?.id}:${newState.podcast?.sources.hashCode()}"

            HeroStyle.VIDEO ->
                "musicvideo:${newState.musicVideo?.id}:${newState.musicVideo?.meta.hashCode()}"

            HeroStyle.PLAYLIST ->
                "playlist:${newState.playlist?.localId}:${newState.playlist?.name}:${newState.playlist?.itemCount}:${newState.playlist?.thumbnail}"
        }

        if (key == lastKey) return

        lastKey = key
        _state.value = newState
    }
}
