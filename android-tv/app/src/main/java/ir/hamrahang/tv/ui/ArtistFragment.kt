
package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import kotlinx.coroutines.launch
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.data.song.SongRepository


class ArtistFragment : Fragment(R.layout.fragment_artist) {
    private lateinit var heroWidget: HeroWidget
    private lateinit var heroContainer: View
    private lateinit var rows: VerticalGridView
    private var artistId: String? = null


    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        artistId = arguments?.getString(ARG_ARTIST_ID)
    }

    override fun onViewCreated(
        view: View,
        savedInstanceState: Bundle?
    ) {
        super.onViewCreated(view, savedInstanceState)
        val heroView = view.findViewById<View>(R.id.heroContainer)

        heroWidget =
            HeroWidget(
                heroView,
                viewLifecycleOwner
            )
        heroWidget.showPlayButton()
        heroWidget.setPlayClick {
            // Play All Artist
        }

        rows = view.findViewById(R.id.listRows)
        rows.setNumColumns(1)
        rows.setItemAlignmentOffset(0)
        rows.setItemAlignmentOffsetPercent(0f)
        rows.setWindowAlignmentOffsetPercent(0f)
        rows.setWindowAlignment( androidx.leanback.widget.BaseGridView.WINDOW_ALIGN_NO_EDGE)
        rows.setItemAlignmentOffsetWithPadding(true)
        rows.windowAlignment = androidx.leanback.widget.BaseGridView.WINDOW_ALIGN_NO_EDGE
        rows.itemAlignmentOffset = 0
        rows.itemAlignmentOffsetPercent = 0f

        heroContainer = view.findViewById(R.id.heroContainer)

        loadArtist()
    }
    private val repository = ArtistRepository()
    private fun loadArtist() {

        val id = artistId ?: return

        viewLifecycleOwner.lifecycleScope.launch {

            val artist = repository.getArtist(id)
            BackgroundManager.set(artist.thumb)
            HeroManager.show(HeroContent.ArtistHero(artist))

            heroWidget.showPlayButton()
            heroWidget.requestPlayButtonFocus()

            val songs = SongRepository().getByIds(artist.songIds)
            val albums = AlbumRepository().getByIds(artist.albumIds)
            val artistVideos = VideoRepository().getByIds((artist.musicVideoIds + artist.videoIds).distinct())
            val musicvideos = artistVideos.filter { it.isMusicVideo }
            val videos = artistVideos.filterNot { it.isMusicVideo }

            val rowsData = ContentRows {

                musicvideos(
                    items = musicvideos,
                    title = "Music Videos",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title ?: "",
                    sessionSource = PlaybackSource.ARTIST
                )

                videos(
                    items = videos,
                    title = "Videos",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title ?: "",
                    sessionSource = PlaybackSource.ARTIST
                )

                songs(
                    items = songs,
                    title = "Top Songs",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title ?: "",
                    sessionSource = PlaybackSource.ARTIST
                )

                albums(
                    items = albums,
                    title = "Albums",
                    //showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title ?: "",
                    sessionSource = PlaybackSource.ARTIST
                )


            }

/*
            val rowsData = ContentRows {

                videos(
                    items = artist.videos,
                    title = "Music Videos",
                    sessionId = artist.id,
                    sessionTitle = artist.title?:"",
                    sessionSource = PlaybackSource.ARTIST
                )

                songs(
                    items = artist.songs,
                    title = "Top Songs",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title?:"",
                    sessionSource = PlaybackSource.ARTIST
                )

                albums(
                    items = artist.albums,
                    title = "Albums",
                    sessionId = artist.id,
                    sessionTitle = artist.title?:"",
                    sessionSource = PlaybackSource.ARTIST
                )


            }*/

            rows.adapter = ContentRowsAdapter(

                rows = rowsData,

                onHeroFocus = { hero ->

                    HeroManager.show(hero)

                },
                onBackgroundChange = {
                    BackgroundManager.set(it)
                },
                onItemClick = { event ->

                    UiActionDispatcher.dispatch(
                        navigator = UiNavigator,
                        event = event
                    )

                }
            )
            rows.post {

                heroWidget.setPlayButtonKeyListener(rows)

            }
        }

    }

    override fun onDestroyView() {
        heroWidget.hidePlayButton()
        super.onDestroyView()
    }

    companion object {

        private const val ARG_ARTIST_ID = "artist_id"

        fun newInstance(id: String): ArtistFragment {

            return ArtistFragment().apply {

                arguments = Bundle().apply {
                    putString(ARG_ARTIST_ID, id)
                }
            }
        }
    }
    private val margin by lazy {

        resources.getDimensionPixelSize(R.dimen._18dp)

    }

}
