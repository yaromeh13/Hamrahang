package ir.hamrahang.app.playback

import androidx.media3.common.ForwardingPlayer
import androidx.media3.common.Player
import androidx.media3.session.MediaSession
import androidx.media3.session.MediaSessionService
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.player.PlayerManager

/**
 * Owns the mobile ExoPlayer for the lifetime of background playback.
 * PlaybackManager remains the single source of truth for queue, repeat and shuffle.
 */
class MobilePlaybackService : MediaSessionService() {
    private var mediaSession: MediaSession? = null

    override fun onCreate() {
        super.onCreate()
        val exoPlayer = PlayerManager.getPlayer(applicationContext)
        val sessionPlayer = object : ForwardingPlayer(exoPlayer) {
            override fun getAvailableCommands(): Player.Commands =
                super.getAvailableCommands().buildUpon()
                    .add(Player.COMMAND_SEEK_TO_NEXT)
                    .add(Player.COMMAND_SEEK_TO_PREVIOUS)
                    .build()

            override fun seekToNextMediaItem() {
                PlaybackManager.next()
            }

            override fun seekToPreviousMediaItem() {
                PlaybackManager.previous()
            }
        }
        mediaSession = MediaSession.Builder(this, sessionPlayer).build()
    }

    override fun onGetSession(controllerInfo: MediaSession.ControllerInfo): MediaSession? =
        mediaSession

    override fun onDestroy() {
        mediaSession?.release()
        mediaSession = null
        PlayerManager.release()
        super.onDestroy()
    }
}
