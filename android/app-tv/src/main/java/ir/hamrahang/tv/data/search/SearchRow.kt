package ir.hamrahang.tv.ui.search

import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.search.SearchSection
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.rows.ContentRow
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.util.str
object SearchRows {

    fun build(
        data: List<SearchSection>
    ): List<ContentRow> {

        return ContentRows {

            data.forEach { section ->

                when (section) {

                    is SearchSection.Songs ->

                        songs(
                            items = section.items,
                            title = R.string.tab_tracks.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )

                    is SearchSection.MusicVideos ->

                        musicvideos(
                            items = section.items,
                            title = R.string.tab_videos.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )

                    is SearchSection.Podcasts ->

                        podcasts(
                            items = section.items,
                            title = R.string.tab_tracks.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )

                    is SearchSection.Videos ->

                        videos(
                            items = section.items,
                            title = R.string.tab_videos.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )

                    is SearchSection.Artists ->

                        artists(
                            items = section.items,
                            title = R.string.artists.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )

                    is SearchSection.Albums ->

                        albums(
                            items = section.items,
                            title = R.string.tab_albums.str,
                            sessionId = "search",
                            sessionTitle = "Search",
                            sessionSource = PlaybackSource.SEARCH
                        )
                }
            }
        }
    }
}