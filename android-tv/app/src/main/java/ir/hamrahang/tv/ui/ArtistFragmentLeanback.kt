
package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.ImageView
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.layout.HeroLayoutManager
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import kotlinx.coroutines.launch


class ArtistFragmentLeanback : Fragment(R.layout.fragment_artist_leanback) {
    private lateinit var heroWidget: HeroWidget
    private lateinit var txtInfo: TextView
    private lateinit var rows: RecyclerView

    private lateinit var heroContainer: View

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

        txtInfo = view.findViewById(R.id.txtArtistInfo)
        rows = view.findViewById(R.id.listRows)

        rows.layoutManager =
            LinearLayoutManager(requireContext())


        heroContainer = view.findViewById(R.id.heroContainer)

        loadArtist()
    }
    private val repository = ArtistRepository()
    private fun loadArtist() {

        val id = artistId ?: return

        viewLifecycleOwner.lifecycleScope.launch {

            val artist = repository.getArtist(id)
            HeroManager.show(
                HeroContent.ArtistDtlHero(artist)
            )
            heroWidget.showPlayButton()
            heroWidget.requestPlayButtonFocus()

            txtInfo.text =
                "${artist.albums.size} Albums • " +
                        "${artist.songs.size} Songs • " +
                        "${artist.videos.size} Videos"



            val rowsData = ContentRows {

                videos(
                    items = artist.videos,
                    title = "Music Videos",
                    sessionId = artist.id,
                    sessionTitle = artist.name,
                    sessionSource = PlaybackSource.ARTIST
                )

                songs(
                    items = artist.songs,
                    title = "Top Songs",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.name,
                    sessionSource = PlaybackSource.ARTIST
                )

                albums(
                    items = artist.albums,
                    title = "Albums",
                    sessionId = artist.id,
                    sessionTitle = artist.name,
                    sessionSource = PlaybackSource.ARTIST
                )


            }

            rows.adapter = ContentRowsAdapter(

                rows = rowsData,

                onHeroFocus = { hero ->

                    HeroManager.show(hero)

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

        fun newInstance(id: String): ArtistFragmentLeanback {

            return ArtistFragmentLeanback().apply {

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
