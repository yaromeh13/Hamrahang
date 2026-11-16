package ir.hamrahang.tv.ui.rows

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.playback.PlaybackSource

class ContentRowsBuilder {

    private val rows = mutableListOf<ContentRow>()

    fun musicvideos(
        items: List<MusicVideo>,
        title: String? = null,
        showSubtitle: Boolean = true,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.MusicVideos(
            title = title,
            items = items,
            //showSubtitle = showSubtitle,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun videos(
        items: List<Video>,
        title: String? = null,
        showSubtitle: Boolean = true,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.Videos(
            title = title,
            items = items,
            //showSubtitle = showSubtitle,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun songs(
        items: List<Song>,
        title: String? = null,
        showSubtitle: Boolean = true,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.Songs(
            title = title,
            items = items,
            showSubtitle = showSubtitle,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun podcasts(
        items: List<Podcast>,
        title: String? = null,
        showSubtitle: Boolean = true,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.Podcasts(
            title = title,
            items = items,
            //showSubtitle = showSubtitle,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun artists(
        items: List<Artist>,
        title: String? = null,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.Artists(
            title = title,
            items = items,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun albums(
        items: List<Album>,
        title: String? = null,
        sessionId: String,
        sessionTitle: String,
        sessionSource: PlaybackSource
    ) {
        if (items.isEmpty()) return

        rows += ContentRow.Albums(
            title = title,
            items = items,
            sessionId = sessionId,
            sessionTitle = sessionTitle,
            sessionSource = sessionSource
        )
    }

    fun build(): List<ContentRow> = rows
}

fun ContentRows(
    block: ContentRowsBuilder.() -> Unit
): List<ContentRow> {

    return ContentRowsBuilder()
        .apply(block)
        .build()
}

