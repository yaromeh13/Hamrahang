package ir.hamrahang.tv.ui.profile

import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.favorite.FavoriteSection
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.rows.ContentRow
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.util.str

object FavoriteRows {

    fun build(

        data: List<FavoriteSection>

    ): List<ContentRow> {

        return ContentRows {

            data.forEach { section ->

                when (section) {

                    is FavoriteSection.Songs ->

                        songs(

                            items = section.items,

                            title = R.string.tab_tracks.str,

                            sessionId = "favorite",

                            sessionTitle = "Favorites",

                            sessionSource = PlaybackSource.FAVORITES

                        )

                    is FavoriteSection.MusicVideos ->

                        musicvideos(

                            items = section.items,

                            title = R.string.tab_videos.str,

                            sessionId = "favorite",

                            sessionTitle = "Favorites",

                            sessionSource = PlaybackSource.FAVORITES

                        )

                    is FavoriteSection.Videos ->

                        videos(

                            items = section.items,

                            title = R.string.tab_videos.str,

                            sessionId = "favorite",

                            sessionTitle = "Favorites",

                            sessionSource = PlaybackSource.FAVORITES

                        )

                    is FavoriteSection.Artists ->

                        artists(

                            items = section.items,

                            title = R.string.tab_artists.str,

                            sessionId = "favorite",

                            sessionTitle = "Favorites",

                            sessionSource = PlaybackSource.FAVORITES

                        )

                    is FavoriteSection.Albums ->

                        albums(

                            items = section.items,

                            title = R.string.tab_albums.str,

                            sessionId = "favorite",

                            sessionTitle = "Favorites",

                            sessionSource = PlaybackSource.FAVORITES

                        )

                }

            }

        }

    }

}