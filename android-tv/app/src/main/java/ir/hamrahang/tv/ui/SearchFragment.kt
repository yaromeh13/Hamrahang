package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.EditText
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.leanback.widget.VerticalGridView
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.search.SearchSection
import ir.hamrahang.tv.data.local.SearchHistoryStore

import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import ir.hamrahang.tv.ui.search.SearchRows
import ir.hamrahang.tv.ui.search.SearchViewModel
import kotlinx.coroutines.launch
import ir.hamrahang.tv.util.str



class SearchFragment : Fragment(R.layout.fragment_search) {

    private lateinit var heroWidget: HeroWidget

    private lateinit var heroContainer: View
    private val viewModel: SearchViewModel by viewModels()

    private lateinit var tabs: List<TextView>
    private var selectedTab = 0
    private lateinit var rows: VerticalGridView

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val searchInput = view.findViewById<EditText>(R.id.searchInput)

        val tabResult = view.findViewById<TextView>(R.id.tabResult)
        val tabTracks = view.findViewById<TextView>(R.id.tabTracks)
        val tabArtists = view.findViewById<TextView>(R.id.tabArtists)
        val tabMusicVideos = view.findViewById<TextView>(R.id.tabMusicVideos)
        val tabPodcasts = view.findViewById<TextView>(R.id.tabPodcasts)
        val tabVideos = view.findViewById<TextView>(R.id.tabVideos)
        val tabAlbums = view.findViewById<TextView>(R.id.tabAlbums)
        rows = view.findViewById(R.id.searchRecycler)
        tabs = listOf(tabResult, tabTracks, tabArtists, tabMusicVideos ,tabPodcasts,tabVideos, tabAlbums)

        // default tab
        selectTab(0)

        heroContainer =
            view.findViewById(R.id.searchHeroContainer)

        heroWidget =
            HeroWidget(
                heroContainer,
                viewLifecycleOwner
            )

        heroContainer.visibility = View.GONE

        BackgroundManager.reset()
        // focus
        searchInput.isFocusable = true
        searchInput.isFocusableInTouchMode = true
        view.post { searchInput.requestFocus()
            searchInput.setOnFocusChangeListener { _, hasFocus ->

                if (hasFocus) {

                    heroContainer.visibility = View.GONE

                }

            }
        }

        searchInput.setOnKeyListener { _, keyCode, event ->
            if (event.action != android.view.KeyEvent.ACTION_DOWN) return@setOnKeyListener false

            when (keyCode) {
                android.view.KeyEvent.KEYCODE_DPAD_DOWN -> {
                    tabResult.requestFocus()
                    true
                }

                android.view.KeyEvent.KEYCODE_DPAD_LEFT -> {
                    (activity as? MainActivity)?.onReturnToMenu()
                    true
                }

                else -> false
            }
        }

        tabs.forEachIndexed { index, tab ->
            tab.setOnClickListener { selectTab(index) }
            tab.setOnFocusChangeListener { _, hasFocus ->

                if (hasFocus) {

                    heroContainer.visibility = View.GONE

                }

            }
        }



        rows.setNumColumns(1)

        rows.setItemAlignmentOffset(0)
        rows.setItemAlignmentOffsetPercent(0f)

        rows.setWindowAlignmentOffsetPercent(0f)
        rows.setWindowAlignment(
            androidx.leanback.widget.BaseGridView.WINDOW_ALIGN_NO_EDGE
        )

        rows.setItemAlignmentOffsetWithPadding(true)
        // search
        searchInput.setOnEditorActionListener { v, _, _ ->
            val query = v.text.toString()
            SearchHistoryStore.add(requireContext(), query)
            viewModel.search(query)
            true
        }

        // 🔥 ONLY DATA FLOW
        lifecycleScope.launch {

            viewModel.result.collect { sections ->

                fullData = sections
                heroContainer.visibility = View.GONE
                showRows(
                    applyTabFilter(selectedTab, sections)
                )
            }
        }
    }

    // 🔥 NEW SIMPLE STORAGE (NOT Map, NOT SearchItem)
    private var fullData: List<SearchSection> = emptyList()

    private fun selectTab(index: Int) {

        selectedTab = index

        tabs.forEachIndexed { i, tab ->
            tab.isSelected = i == index
        }

        showRows(
            applyTabFilter(index, fullData)
        )
        /*if (index == 0) {

            heroContainer.visibility = View.GONE

        } else {

            heroContainer.visibility = View.VISIBLE

        }*/
    }
    private fun applyTabFilter(
        index: Int,
        data: List<SearchSection>
    ): List<SearchSection> {

        val filtered = when (index) {

            0 -> data

            1 -> data.filterIsInstance<SearchSection.Songs>()

            2 -> data.filterIsInstance<SearchSection.Artists>()

            3 -> data.filterIsInstance<SearchSection.MusicVideos>()

            4 -> data.filterIsInstance<SearchSection.Podcasts>()

            5 -> data.filterIsInstance<SearchSection.Videos>()

            6 -> data.filterIsInstance<SearchSection.Albums>()

            else -> data
        }

        updateTabsVisibility(data)

        return filtered
    }
    private fun updateTabsVisibility(
        data: List<SearchSection>
    ) {

        tabVisibility(R.id.tabArtists,
            data.filterIsInstance<SearchSection.Artists>()
        )

        tabVisibility(R.id.tabTracks,
            data.filterIsInstance<SearchSection.Songs>()
        )

        tabVisibility(R.id.tabPodcasts,
            data.filterIsInstance<SearchSection.Podcasts>()
        )

        tabVisibility(R.id.tabMusicVideos,
            data.filterIsInstance<SearchSection.MusicVideos>()
        )

        tabVisibility(R.id.tabVideos,
            data.filterIsInstance<SearchSection.Videos>()
        )

        tabVisibility(R.id.tabAlbums,
            data.filterIsInstance<SearchSection.Albums>()
        )
    }

    private fun tabVisibility(id: Int, list: List<*>?) {
        view?.findViewById<View>(id)?.visibility =
            if (list.isNullOrEmpty()) View.GONE else View.VISIBLE
    }
    private fun showRows(
        data: List<SearchSection>
    ) {

        if (data.isEmpty()) {
            rows.adapter = null
            return
        }

        rows.adapter = ContentRowsAdapter(

            rows = SearchRows.build(data),

            onHeroFocus = {

                if (selectedTab == 0) return@ContentRowsAdapter
                //heroContainer.visibility = View.VISIBLE

                HeroManager.show(it)

            },
            onBackgroundChange = {_ ->},
            onItemClick = {

                UiActionDispatcher.dispatch(
                    UiNavigator,
                    it
                )

            }

        )
    }
}


