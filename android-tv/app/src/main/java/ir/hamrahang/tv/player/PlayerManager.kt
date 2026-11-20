package ir.hamrahang.tv.player

import android.content.Context
import android.util.Log
import androidx.media3.common.MediaItem
import androidx.media3.common.PlaybackException
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.playback.PlaybackManager
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.isActive
import kotlinx.coroutines.launch
import androidx.media3.common.C
import androidx.media3.exoplayer.trackselection.DefaultTrackSelector

import ir.hamrahang.tv.ui.message.MessageManager
import kotlinx.coroutines.SupervisorJob

object PlayerManager {

    private var player: ExoPlayer? = null
    private var currentMedia: PlayerMedia? = null
    private var currentUrlIndex = 0
    private var progressJob: Job? = null
    private var loadingTimeoutJob: Job? = null
    private val supervisorJob = SupervisorJob()
    private val scope = CoroutineScope(Dispatchers.Main + supervisorJob)

    private fun startProgressUpdates() {
        progressJob?.cancel()
        progressJob = scope.launch {
            while (isActive) {
                publishUi()
                delay(150)
            }
        }
    }

    private fun startLoadingTimeout() {

        loadingTimeoutJob?.cancel()

        loadingTimeoutJob = scope.launch {

            delay(60_000)

            if (PlayerState.uiState.value.playbackState ==
                PlaybackState.LOADING) {
                MessageManager.info(
                    "Connecting..."
                )
                Log.e(
                    "PLAYER",
                    "Loading timeout"
                )

                playNextUrl()

            }

        }

    }
    private fun cancelLoadingTimeout() {
        loadingTimeoutJob?.cancel()
        loadingTimeoutJob = null
    }

    private fun stopProgressUpdates() {
        progressJob?.cancel()
        progressJob = null
    }

    fun getPlayer(context: Context): ExoPlayer {

        if (player != null) return player!!

        val trackSelector = DefaultTrackSelector(context.applicationContext)
        trackSelector.setParameters(
            trackSelector.buildUponParameters()
                .setTrackTypeDisabled(C.TRACK_TYPE_IMAGE, true)
        )

        player =
            ExoPlayer.Builder(context.applicationContext)
                .setTrackSelector(trackSelector)
                .build()

        /*player =
            ExoPlayer.Builder(
                context.applicationContext
            ).build()*/

        player!!.volume = if (PlayerState.uiState.value.muted) 0f else 1f
        PlayerState.setRepeatMode(player!!.repeatMode)
        PlayerState.setShuffle(player!!.shuffleModeEnabled)
        player!!.addListener(object : Player.Listener {
            override fun onPlaybackStateChanged(state: Int) {
                when (state) {
                    Player.STATE_IDLE -> {
                        stopProgressUpdates()
                        PlayerState.setPlaybackState(PlaybackState.IDLE)
                    }

                    Player.STATE_BUFFERING -> {
                        stopProgressUpdates()
                        PlayerState.setPlaybackState(PlaybackState.LOADING)
                        startLoadingTimeout()
                    }

                    Player.STATE_READY -> {
                        cancelLoadingTimeout()
                        if (player!!.isPlaying) {
                            PlayerState.setPlaybackState(PlaybackState.PLAYING)
                            startProgressUpdates()
                        } else {
                            PlayerState.setPlaybackState(PlaybackState.PAUSED)
                            stopProgressUpdates()
                        }
                        publishUi()
                    }

                    Player.STATE_ENDED -> {
                        cancelLoadingTimeout()
                        stopProgressUpdates()
                        PlaybackManager.next()
                    }
                }
            }

            override fun onIsPlayingChanged(
                isPlaying: Boolean
            ) {

                PlayerState.setPlaybackState(

                    if (isPlaying)
                        PlaybackState.PLAYING
                    else
                        PlaybackState.PAUSED

                )

                if (isPlaying) {

                    startProgressUpdates()

                } else {

                    stopProgressUpdates()

                }

                publishUi()

            }

            override fun onPlayerError(
                error: PlaybackException
            ) {
                cancelLoadingTimeout()

                Log.e(
                    "PLAYER_ERROR",
                    error.message ?: "Unknown"
                )

                playNextUrl()
            }

            override fun onRepeatModeChanged(repeatMode: Int) {

                PlayerState.setRepeatMode(repeatMode)

            }

            override fun onShuffleModeEnabledChanged(shuffleModeEnabled: Boolean) {

                PlayerState.setShuffle(shuffleModeEnabled)

            }
        })

        return player!!
    }
    fun play(
        context: Context,
        media: PlayerMedia
    ) {
        getPlayer(context)
        currentMedia = media
        currentUrlIndex = 0

        //publishStatic()
       // PlayerState.setMediaType(media.type)

        /*PlayerState.setSong(
            id = media.id,
            title = media.title,
            artist = media.artist,
            artwork = media.artwork
        )*/
        PlayerState.updatePlaybackInfo {
            copy(
                visible = true,
                mediaId = media.id,
                mediaType = media.type,
                artwork = media.artwork,
                title = media.title ?: "",
            )
        }

        PlayerState.update {
            copy(
                playbackState = PlaybackState.LOADING,
                position = 0L,
                duration = 0L
            )
        }
        //PlayerState.setPlaybackState(PlaybackState.LOADING)

        MessageManager.info("Connecting...")

        if (media.sources.isEmpty()) {

            PlayerState.setPlaybackState(
                PlaybackState.ERROR
            )

            return
        }

        playCurrentUrl()
    }
    private fun playCurrentUrl(
        startPositionMs: Long = 0L,
        autoplay: Boolean = true
    ) {
        val media = currentMedia ?: return
        val source = media.sources.getOrNull(currentUrlIndex)
        if (source == null) {
            PlayerState.setPlaybackState(PlaybackState.ERROR)
            return
        }
        PlayerState.updatePlaybackInfo {
            copy(selectedSource = source)
        }
        player?.apply {
            stop()
            clearMediaItems()
            setMediaItem(
                MediaItem.fromUri(source.url),
                startPositionMs.coerceAtLeast(0L)
            )
            playWhenReady = autoplay
            PlayerState.setPlaybackState(PlaybackState.LOADING)
            prepare()
        }
    }

    /*private fun publishStatic() {

        val media = currentMedia ?: return

        PlayerState.update {

            copy(

                visible = true,

                mediaId = media.id,

                artwork = media.artwork,

                title = media.title ?: "",

                artist = media.artist

            )
        }
    }*/
    private fun publishUi() {
        //val media = currentMedia ?: return
        PlayerState.update {
            if (
                duration == getDuration() &&
                position == getCurrentPosition()
            ) {
                this
            } else {
                copy(
                    duration = getDuration(),
                    position = getCurrentPosition()
                )
            }
        }
    }
    fun toggleRepeat() {
        val p = player ?: return
        p.repeatMode =
            if (p.repeatMode == Player.REPEAT_MODE_OFF)
                Player.REPEAT_MODE_ALL
            else
                Player.REPEAT_MODE_OFF
    }
    fun toggleMute() {
        val muted = !PlayerState.uiState.value.muted
        player?.volume = if (muted) 0f else 1f
        PlayerState.setMute(muted)
    }
    fun toggle() {
        val p = player ?: return
        if (p.isPlaying) {
            p.pause()
        } else {
            p.play()
        }
        // Listener خودش state رو آپدیت میکنه
    }

    fun pause() {
        player?.pause()
    }

    fun resume() {
        player?.play()
    }

    fun stop() {
        cancelLoadingTimeout()
        stopProgressUpdates()
        player?.stop()
        PlayerState.update {
            copy(
                playbackState = PlaybackState.IDLE,
                //visible = false,
                position = 0L,
                duration = 0L
            )
        }
    }

    fun release() {
        cancelLoadingTimeout()
        stopProgressUpdates()
        player?.release()
        player = null
        currentMedia = null
        currentUrlIndex = 0
        PlayerState.reset()
    }

    fun seekTo(position: Long) {
        player?.seekTo(position)
        publishUi()
    }

    fun getDuration(): Long {
        val d = player?.duration ?: 0L
        return if (d > 0) d else 0L
    }

    fun getCurrentPosition(): Long {
        return player?.currentPosition ?: 0L
    }

    fun isPlaying(): Boolean {
        return PlayerState.uiState.value.playbackState == PlaybackState.PLAYING
    }

    fun getCurrentMedia(): PlayerMedia? {
        return currentMedia
    }

    fun isCurrent(media: PlayerMedia): Boolean {
        return currentMedia?.id == media.id
    }
    fun toggleShuffle() {
        val p = player ?: return
        p.shuffleModeEnabled = !p.shuffleModeEnabled
    }

    private fun playNextUrl() {
        val media = currentMedia ?: return
        if (currentUrlIndex < media.sources.lastIndex) {
            currentUrlIndex++
            MessageManager.warning(
                "Trying another source..."
            )
            playCurrentUrl()
        } else {
            MessageManager.error(
                "This source failed."
            )
            //player?.stop()
            //player?.clearMediaItems()

            /*PlayerState.setPlaybackState(
                PlaybackState.ERROR
            )*/
            PlaybackManager.next()
        }
    }

    fun switchToSource(
        source: MediaSource
    ) {
        val media = currentMedia ?: return

        val index = media.sources.indexOfFirst {
            it.url == source.url
        }

        if (index == -1 || index == currentUrlIndex) {
            return
        }

        val activePlayer = player ?: return
        val resumePosition = activePlayer.currentPosition.coerceAtLeast(0L)
        val resumePlayback = activePlayer.playWhenReady

        currentUrlIndex = index
        PlayerState.updatePlaybackInfo {
            copy(selectedSource = source)
        }

        playCurrentUrl(
            startPositionMs = resumePosition,
            autoplay = resumePlayback
        )
    }
}

enum class MediaType {
    AUDIO,
    VIDEO
}

data class PlayerMedia(
    val id: String?,
    val title: String?,
    val artist: String,
    val artwork: String?,
    val sources: List<MediaSource>,
    val type: MediaType,
    val contentType: ContentType? = null
)

