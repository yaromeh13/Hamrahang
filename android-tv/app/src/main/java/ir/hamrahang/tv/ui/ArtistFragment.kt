
package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.fragment.app.Fragment
import com.google.android.material.button.MaterialButton
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import kotlinx.coroutines.launch
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.song.SongRepository


class ArtistFragment : Fragment(R.layout.fragment_artist) {
    private lateinit var heroWidget: HeroWidget
    private lateinit var heroContainer: View
    private lateinit var rows: VerticalGridView
    private var artistId: String? = null
    private lateinit var followButton: MaterialButton


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
        followButton = heroView.findViewById(R.id.btnFollowArtist)

        heroWidget =
            HeroWidget(
                heroView,
                viewLifecycleOwner
            )
        heroWidget.showPlayButton()
        heroWidget.showFollowButton()
        followButton.nextFocusLeftId = R.id.btnPlayArtist
        heroWidget.setFollowClick { toggleArtistFollow() }
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
            bindArtistFollowState(artist.id)
            BackgroundManager.set(artist.heroBackground)
            HeroManager.show(HeroContent.ArtistHero(artist))

            heroWidget.showPlayButton()
            heroWidget.requestPlayButtonFocus()

            val songs = SongRepository().getByIds(artist.songIds)
            val albums = AlbumRepository().getByIds(artist.albumIds)
            val videos = VideoRepository().getByIds(artist.videoIds)
            val musicvideos = MusicVideoRepository().getByIds(artist.musicVideoIds)

            val rowsData = ContentRows {

                videos(
                    items = videos,
                    title = "Videos",
                    showSubtitle = false,
                    sessionId = artist.id,
                    sessionTitle = artist.title ?: "",
                    sessionSource = PlaybackSource.ARTIST
                )

                musicvideos(
                    items = musicvideos,
                    title = "Music Videos",
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

                onHeroFocus = { /* Keep the artist identity hero fixed while browsing its rows. */ },
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
                heroWidget.setFollowButtonKeyListener(rows)
            }
        }

    }

    private fun toggleArtistFollow() {
        val id = artistId?.toLongOrNull()
        if (id == null) {
            MessageManager.info(getString(R.string.artist_follow_unavailable))
            return
        }
        viewLifecycleOwner.lifecycleScope.launch {
            val followed = FavoriteManager.toggle(id, ContentType.ARTIST)
            updateFollowButton(followed)
            MessageManager.info(getString(if (followed) R.string.artist_followed else R.string.artist_unfollowed))
        }
    }

    private fun bindArtistFollowState(id: String) {
        val mediaId = id.toLongOrNull() ?: return
        viewLifecycleOwner.lifecycleScope.launch {
            updateFollowButton(FavoriteManager.isFavorite(mediaId, ContentType.ARTIST))
        }
    }

    private fun updateFollowButton(followed: Boolean) {
        heroWidget.setArtistFollowed(followed)
        followButton.contentDescription = getString(
            if (followed) R.string.following else R.string.follow
        )
    }

    override fun onDestroyView() {
        heroWidget.hidePlayButton()
        heroWidget.hideFollowButton()
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
