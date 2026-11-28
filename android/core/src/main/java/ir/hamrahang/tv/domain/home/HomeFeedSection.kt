package ir.hamrahang.tv.domain.home

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.playback.PlaybackSource

/**
 * UI-independent groups of content assembled for a Home feed.
 *
 * Apps may map these domain sections to their own presentation models.
 */
sealed class HomeFeedSection(
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
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)

    data class Songs(
        override val title: String?,
        val items: List<Song>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)

    data class Artists(
        override val title: String?,
        val items: List<Artist>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)

    data class Albums(
        override val title: String?,
        val items: List<Album>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)

    data class Podcasts(
        override val title: String?,
        val items: List<Podcast>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)

    data class Videos(
        override val title: String?,
        val items: List<Video>,
        override val sessionId: String,
        override val sessionTitle: String,
        override val sessionSource: PlaybackSource
    ) : HomeFeedSection(title, sessionId, sessionTitle, sessionSource)
}
