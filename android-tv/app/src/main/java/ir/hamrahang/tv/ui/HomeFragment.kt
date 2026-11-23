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