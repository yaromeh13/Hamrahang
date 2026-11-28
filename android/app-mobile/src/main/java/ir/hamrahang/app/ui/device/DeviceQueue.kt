package ir.hamrahang.app.ui.device

import android.content.Context
import android.content.Intent
import android.net.Uri
import ir.hamrahang.app.ui.player.VideoActivity
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerMedia

data class DeviceTrack(
    val uri: Uri,
    val title: String,
    val subtitle: String,
    val video: Boolean
)

/** Queue for files stored on the phone. Online playback keeps using PlaybackManager. */
object DeviceQueue {

    private var tracks: List<DeviceTrack> = emptyList()
    private var index: Int = -1

    fun isActive(): Boolean =
        PlayerManager.getCurrentMedia()?.id?.startsWith(ID_PREFIX) == true

    fun play(context: Context, items: List<DeviceTrack>, start: Int) {
        if (start !in items.indices) return
        tracks = items
        index = start
        val track = items[start]
        PlayerManager.play(
            context,
            PlayerMedia(
                id = ID_PREFIX + track.uri,
                title = track.title,
                artist = track.subtitle,
                artwork = null,
                sources = listOf(MediaSource(url = track.uri.toString())),
                type = if (track.video) MediaType.VIDEO else MediaType.AUDIO
            )
        )
        if (track.video) {
            context.startActivity(Intent(context, VideoActivity::class.java))
        }
    }

    fun next(context: Context) {
        if (!isActive()) {
            PlaybackManager.next()
            return
        }
        val next = index + 1
        if (next <= tracks.lastIndex) play(context, tracks, next)
    }

    fun previous(context: Context) {
        if (!isActive()) {
            PlaybackManager.previous()
            return
        }
        if (PlayerManager.getCurrentPosition() > 3_000L) {
            PlayerManager.restartCurrent()
            return
        }
        val previous = index - 1
        if (previous >= 0) play(context, tracks, previous) else PlayerManager.restartCurrent()
    }

    private const val ID_PREFIX = "device:"
}
