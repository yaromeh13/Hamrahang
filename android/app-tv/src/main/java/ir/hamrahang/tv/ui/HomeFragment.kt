package ir.hamrahang.tv.ui

import android.os.Bundle
import android.util.Log
import android.view.View
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.HorizontalGridView
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.contract.Focusable
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.home.HomeViewModel
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import kotlinx.coroutines.launch


class HomeFragment :
    Fragment(R.layout.fragment_home),
    Focusable {

    private lateinit var heroWidget: HeroWidget
    private val viewModel: HomeViewModel by viewModels()
    private lateinit var adapter: ContentRowsAdapter
    private lateinit var recycler: VerticalGridView
    private var initialRowsFocusRequested = false

    override fun onResume() {
        super.onResume()
        refreshPlaylists()
    }

    /** Refresh cached and remote Home rows without recreating the screen. */
    fun refreshAfterNetworkRecovery() {
        if (!::adapter.isInitialized) return
        viewModel.refreshContent()
        viewModel.refreshPlaylists()
    }

    /** Refresh the Home playlist rows after account changes. */
    fun refreshPlaylists() {
        if (::adapter.isInitialized) viewModel.refreshPlaylists()
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        // HERO
        val heroView = view.findViewById<View>(R.id.heroContainer)
        heroWidget = HeroWidget(heroView,viewLifecycleOwner)

        // VERTICAL ROWS
        recycler = view.findViewById(R.id.rowsRecycler)
        recycler.setNumColumns(1)
        recycler.setItemAlignmentOffset(0)
        recycler.setItemAlignmentOffsetPercent(0f)
        recycler.setWindowAlignmentOffsetPercent(0f)
        recycler.setWindowAlignment(androidx.leanback.widget.BaseGridView.WINDOW_ALIGN_NO_EDGE)
        recycler.windowAlignment = VerticalGridView.WINDOW_ALIGN_NO_EDGE
        recycler.itemAlignmentOffset = 0
        recycler.itemAlignmentOffsetPercent = 0f
        recycler.setItemAlignmentOffsetWithPadding(true)

        // ===== ADAPTER =====
        adapter = ContentRowsAdapter(
            emptyList(),
            onHeroFocus = { heroContent ->
                HeroManager.show(heroContent)
                bindHomeHeroActions(heroContent)
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
        recycler.adapter = adapter

        // ===== ROWS =====
        viewLifecycleOwner.lifecycleScope.launch {
            viewModel.rows.collect { rows ->
                adapter.submit(rows)
                if (!initialRowsFocusRequested && rows.isNotEmpty()) {
                    initialRowsFocusRequested = true
                    requestHomeFocus()
                }
            }
        }
    }

    private fun bindHomeHeroActions(heroContent: HeroContent) {
        val artist = (heroContent as? HeroContent.ArtistHero)?.artist
        if (artist == null) {
            heroWidget.hideFollowButton()
            return
        }

        heroWidget.showFollowButton()
        heroViewFollowLeftToArtistCover()
        heroWidget.setFollowClick {
            val mediaId = artist.id.toLongOrNull()
            if (mediaId == null) {
                MessageManager.info(getString(R.string.artist_follow_unavailable))
            } else {
                viewLifecycleOwner.lifecycleScope.launch {
                    val followed = FavoriteManager.toggle(mediaId, ContentType.ARTIST)
                    if (HeroManager.engine.state.value?.artist?.id == artist.id) {
                        heroWidget.setArtistFollowed(followed)
                    }
                    MessageManager.info(
                        getString(if (followed) R.string.artist_followed else R.string.artist_unfollowed)
                    )
                }
            }
        }
        viewLifecycleOwner.lifecycleScope.launch {
            val followed = artist.id.toLongOrNull()?.let {
                FavoriteManager.isFavorite(it, ContentType.ARTIST)
            } ?: false
            if (HeroManager.engine.state.value?.artist?.id == artist.id) {
                heroWidget.setArtistFollowed(followed)
            }
        }
        heroWidget.setFollowButtonKeyListener(recycler)
    }

    private fun heroViewFollowLeftToArtistCover() {
        view?.findViewById<View>(R.id.btnFollowArtist)?.nextFocusLeftId = R.id.artistCover
    }

    private fun requestHomeFocus() {
        recycler.post {
            recycler.setSelectedPosition(0)
            recycler.post {
                val rowView =
                    recycler
                        .findViewHolderForAdapterPosition(0)
                        ?.itemView
                val horizontal = rowView?.findViewById<HorizontalGridView>(R.id.horizontalRecycler)
                horizontal?.post {
                    horizontal.setSelectedPosition(0)
                    horizontal.requestFocus()
                }
            }
        }
    }

    override fun requestInitialFocus() {
        requestHomeFocus()
    }

}