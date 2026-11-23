package ir.hamrahang.tv.ui.rows

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.playback.PlaybackSource

sealed class ContentRow(

    open val title: String?,

    open val sessionId: String,

    open val sessionTitle: String,

    open val sessionSource: PlaybackSource

) {

    data class MusicVideos(

        override val title: String?,

        val items: List<MusicVideo>,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Songs(

        override val title: String?,

        val items: List<Song>,

        val showSubtitle: Boolean = true,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Artists(

        override val title: String?,

        val items: List<Artist>,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Albums(

        override val title: String?,

        val items: List<Album>,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Podcasts(

        override val title: String?,

        val items: List<Podcast>,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Videos(

        override val title: String?,

        val items: List<Video>,

        override val sessionId: String,

        override val sessionTitle: String,

        override val sessionSource: PlaybackSource

    ) : ContentRow(
        title,
        sessionId,
        sessionTitle,
        sessionSource
    )

    data class Playlists(
        override val title: String?,
        val items: List<PlaylistEntity>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : ContentRow(title, sessionId, sessionTitle, sessionSource)
}