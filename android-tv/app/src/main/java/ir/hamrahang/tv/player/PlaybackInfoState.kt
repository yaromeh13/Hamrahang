package ir.hamrahang.tv.player

import android.R
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.song.Song

data class PlaybackInfoState(

    val visible: Boolean = false,
    val mediaId: String? = null,
    val mediaType: MediaType? = null,
    val artwork: Any? = null,
    val title: String = "",
    val song: Song? = null,
    val album: Album? = null,
    val artists: List<Artist> = emptyList(),
    val lyrics: String? = null,
    val liked: Boolean = false,
    val favorite: Boolean = false,
    val downloaded: Boolean = false,
    val metadata: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList(),
    val selectedSource: MediaSource? = null
)