package ir.hamrahang.tv.playback

import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.player.PlayerMedia

data class PlaybackItem(
    val id: String,
    val title: String,
    val artwork: Any?,
    val artists: List<Artist>,
    val contentType: ContentType = ContentType.SONG,
    val playCount: Long? = null,
    /** Non-null only for a Live station; keeps its stream data inside the queue item. */
    val liveMedia: PlayerMedia? = null
)
