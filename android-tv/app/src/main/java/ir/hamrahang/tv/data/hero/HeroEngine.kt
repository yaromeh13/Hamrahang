package ir.hamrahang.tv.hero

import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

class HeroEngine {

    private val _state = MutableStateFlow<HeroState?>(null)
    val state: StateFlow<HeroState?> = _state

    private var lastState: HeroState? = null
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

            is HeroEvent.AlbumFocused -> return(
                    return
                    )
            is HeroEvent.SongFocused -> HeroState(
                style = HeroStyle.SONG,
                song = event.song
            )
        }
        val key = when (newState.style) {

            HeroStyle.ARTIST ->
                "artist:${newState.artist?.id}:${newState.artist?.meta.hashCode()}"

            HeroStyle.SONG ->
                "song:${newState.song?.id}:${newState.song?.sources.hashCode()}"

            HeroStyle.VIDEO ->
                "musicvideo:${newState.musicVideo?.id}:${newState.musicVideo?.meta.hashCode()}"

        }
        if (key == lastKey) return

        lastKey = key
        _state.value = newState
        // 🔥 مهم: جلوگیری از آپدیت تکراری
       // if (newState == lastState) return

        //lastState = newState
        //_state.value = newState
    }
}