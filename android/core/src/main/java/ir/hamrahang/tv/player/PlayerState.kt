package ir.hamrahang.tv.player

import androidx.media3.common.Player
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update

enum class PlaybackState {
    IDLE,
    LOADING,
    PLAYING,
    PAUSED,
    ERROR
}

data class PlayerUiState(

    val playbackState: PlaybackState = PlaybackState.IDLE,
    val duration: Long = 0L,
    val position: Long = 0L,
    val shuffle: Boolean = false,
    val repeatMode: Int = Player.REPEAT_MODE_OFF,
    val muted: Boolean = false,
    val fullscreen: Boolean = false,
    val playlistVisible: Boolean = true,
    val lyricsVisible: Boolean = false,
    val playerlyricsVisible: Boolean = true,
    val headerVisible: Boolean = true,
    val controllerVisible: Boolean = false,
    val controllerPinned: Boolean = false,
    val rotatingHeaderEnabled: Boolean = false,
    val videoImageEnabled: Boolean = true,   // سویچ «تصویر»
    val videoCoverEnabled: Boolean = false,  // سویچ «کاور»

)
object PlayerState {
    private val _playbackInfo = MutableStateFlow(PlaybackInfoState())
    val playbackInfo = _playbackInfo.asStateFlow()
    private val _uiState = MutableStateFlow(PlayerUiState())
    val uiState = _uiState.asStateFlow()
    fun update(block: PlayerUiState.() -> PlayerUiState) {_uiState.update { current -> current.block()}}
    fun updatePlaybackInfo(block: PlaybackInfoState.() -> PlaybackInfoState) {_playbackInfo.update { current -> current.block()}
    }

    /*fun updateMetadata(
        song: Song,
        album: Album?,
        artists: List<Artist>,
        liked: Boolean,
        favorite: Boolean,
        downloaded: Boolean

    ) {
        val meta = PlayerMetadata(
            release = song.meta?.rls,
            playCount = song.meta?.ncp,
            albumName = album?.displayTitle(),
            //genres = song.meta?,//.genres ?: emptyList(),
            //language = song.meta?.lng,
            //year = song.meta?.year
        )
        update {
            copy(
                song = song,
                album = album,
                artists = artists,
                liked = liked,
                favorite = favorite,
                downloaded = downloaded
            )
        }
    }*/

    fun reset() {
        _uiState.value = PlayerUiState()
        _playbackInfo.value = PlaybackInfoState()
    }

    /*fun show(artwork: Any?) {
        update { copy(visible = true, artwork = artwork) }
    }*/

    /*fun hide() {
        update { copy(visible = false) }
    }*/
/*
    fun setSong(id: String?, title: String?, artist: String, artwork: Any?) {
        update {
            copy(
                title = title ?: "",
                artist = artist,
                artwork = artwork
            )
        }
    }
*/
    /*fun setArtwork(source: Any?) {
        _uiState.update { it.copy(artwork = source) }
    }*/

    fun setProgress(position: Long, duration: Long) {
        update { copy(position = position, duration = duration) }
    }

    fun setPlaylistVisible(visible: Boolean) {
        update { copy(playlistVisible = visible) }
    }

    fun setLyricsVisible(visible: Boolean) {
        update { copy(lyricsVisible = visible) }
    }

    fun setMute(mute: Boolean) {
        update { copy(muted = mute) }
    }

    fun setShuffle(shuffle: Boolean) {
        update { copy(shuffle = shuffle) }
    }

    fun setFullscreen(fullscreen: Boolean) {
        update { copy(fullscreen = fullscreen) }
    }

    fun setRepeatMode(repeatMode: Int) {
        update { copy(repeatMode = repeatMode) }
    }

    fun hideFullscreen() {
        //update { copy(fullscreen = false, visible = false) }
        update {copy(fullscreen = false)}
    }

    fun setPlaybackState(state: PlaybackState) {
        update { copy(playbackState = state) }
    }

    fun setControllerPinned(pinned: Boolean) {
        update { copy(controllerPinned = pinned) }
    }

    fun setHeaderVisible(visible: Boolean) {
        update { copy(headerVisible = visible) }
    }

    fun setControllerVisible(visible: Boolean) {
        update { copy(controllerVisible = visible) }
    }

    fun setPlayerLyricsVisible(visible: Boolean) {
        update { copy(playerlyricsVisible = visible) }
    }

    /*fun setMediaType(type: MediaType) {
        update { copy(mediaType = type) }
    }*/

    fun setRotatingHeaderEnabled(enabled: Boolean) {
        update { copy(rotatingHeaderEnabled = enabled) }
    }

    fun setVideoImageEnabled(enabled: Boolean) {
        update { copy(videoImageEnabled = enabled) }
    }

    fun setVideoCoverEnabled(enabled: Boolean) {
        update { copy(videoCoverEnabled = enabled) }
    }
}
