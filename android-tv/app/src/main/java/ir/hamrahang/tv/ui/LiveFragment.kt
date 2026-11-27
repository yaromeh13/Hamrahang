package ir.hamrahang.tv.ui

import android.content.Context
import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.view.inputmethod.EditorInfo
import android.view.inputmethod.InputMethodManager
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.HorizontalGridView
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.R
import ir.hamrahang.tv.contract.Focusable
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.data.live.LiveRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Job
import kotlinx.coroutines.launch

class LiveFragment : Fragment(R.layout.fragment_live), Focusable {

    private val repository = LiveRepository()
    private var adapter: LiveRowsAdapter? = null
    private var recycler: VerticalGridView? = null
    private var searchInput: EditText? = null
    private var searchClear: TextView? = null
    private var searchSubmit: TextView? = null
    private var loadJob: Job? = null
    private var searchJob: Job? = null
    private var initialFocusPending = false
    private var searchApplied = false
    private var baseRows: List<LiveItem> = emptyList()

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        recycler = view.findViewById(R.id.liveRowsRecycler)
        searchInput = view.findViewById(R.id.liveSearchInput)
        searchClear = view.findViewById(R.id.liveSearchClear)
        searchSubmit = view.findViewById(R.id.liveSearchSubmit)

        recycler?.apply {
            setNumColumns(1)
            setItemAlignmentOffset(0)
            setItemAlignmentOffsetPercent(0f)
            setItemAlignmentOffsetWithPadding(true)
            setWindowAlignmentOffset(0)
            setWindowAlignmentOffsetPercent(0f)
            setWindowAlignment(BaseGridView.WINDOW_ALIGN_NO_EDGE)
            windowAlignment = VerticalGridView.WINDOW_ALIGN_NO_EDGE
            clipToPadding = false
        }

        adapter = LiveRowsAdapter(
            emptyList(),
            onItemClick = ::playLive,
            onItemFocus = { item ->
                BackgroundManager.set(item.thumb.takeIf { it.isNotBlank() })
            }
        )
        recycler?.adapter = adapter

        bindLiveSearch()
        loadRows()
    }

    private fun bindLiveSearch() {
        val input = searchInput ?: return
        val clear = searchClear ?: return
        val submit = searchSubmit ?: return

        input.addTextChangedListener(object : android.text.TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) = Unit

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                val hasText = !s.isNullOrEmpty()
                clear.visibility = if (hasText) View.VISIBLE else View.GONE

                val rtl = input.layoutDirection == View.LAYOUT_DIRECTION_RTL
                if (rtl) {
                    input.nextFocusLeftId = if (hasText) clear.id else submit.id
                    input.nextFocusRightId = submit.id
                    clear.nextFocusRightId = input.id
                    clear.nextFocusLeftId = submit.id
                    submit.nextFocusRightId = if (hasText) clear.id else input.id
                    submit.nextFocusLeftId = input.id
                } else {
                    input.nextFocusRightId = if (hasText) clear.id else submit.id
                    input.nextFocusLeftId = submit.id
                    clear.nextFocusLeftId = input.id
                    clear.nextFocusRightId = submit.id
                    submit.nextFocusLeftId = if (hasText) clear.id else input.id
                    submit.nextFocusRightId = input.id
                }

                // Clearing the query (by the X button or Backspace) cancels
                // only the submitted search, never the page's initial refresh.
                if (!hasText) {
                    searchJob?.cancel()
                    if (searchApplied) {
                        searchApplied = false
                        render(baseRows)
                    }
                }
            }

            override fun afterTextChanged(s: android.text.Editable?) = Unit
        })

        clear.setOnClickListener {
            searchJob?.cancel()
            input.setText("")
            input.requestFocus()
            hideSearchKeyboard(input)
        }

        submit.setOnClickListener { performLiveSearch() }

        input.setOnFocusChangeListener { focusedView, hasFocus ->
            if (!hasFocus) hideSearchKeyboard(focusedView)
        }

        // Match SearchFragment: DPAD_DOWN leaves the EditText explicitly.
        // Do not let the keyboard or EditText consume the navigation key.
        input.setOnKeyListener { _, keyCode, event ->
            if (event.action != KeyEvent.ACTION_DOWN) return@setOnKeyListener false

            when (keyCode) {
                KeyEvent.KEYCODE_DPAD_DOWN -> {
                    hideSearchKeyboard(input)
                    val target = if (clear.visibility == View.VISIBLE) clear else submit
                    target.requestFocus()
                    true
                }

                KeyEvent.KEYCODE_DPAD_LEFT -> {
                    hideSearchKeyboard(input)
                    (activity as? MainActivity)?.onReturnToMenu()
                    true
                }

                else -> false
            }
        }

        clear.setOnKeyListener { _, keyCode, event ->
            if (event.action == KeyEvent.ACTION_DOWN &&
                keyCode == KeyEvent.KEYCODE_DPAD_UP
            ) {
                input.requestFocus()
                true
            } else {
                false
            }
        }

        submit.setOnKeyListener { _, keyCode, event ->
            if (event.action == KeyEvent.ACTION_DOWN &&
                keyCode == KeyEvent.KEYCODE_DPAD_UP
            ) {
                input.requestFocus()
                true
            } else if (event.action == KeyEvent.ACTION_DOWN &&
                keyCode == KeyEvent.KEYCODE_DPAD_DOWN
            ) {
                hideSearchKeyboard(input)
                initialFocusPending = true
                focusFirstLiveCard()
                true
            } else {
                false
            }
        }

        input.setOnEditorActionListener { _, actionId, event ->
            val enterPressed = event?.let {
                it.keyCode == KeyEvent.KEYCODE_ENTER && it.action == KeyEvent.ACTION_DOWN
            } == true
            if (actionId == EditorInfo.IME_ACTION_SEARCH ||
                actionId == EditorInfo.IME_ACTION_DONE ||
                enterPressed
            ) {
                performLiveSearch()
                true
            } else {
                false
            }
        }

        // Keep remote navigation deterministic even on TV devices that do not
        // infer focus order from the horizontal layout.
        val rtl = input.layoutDirection == View.LAYOUT_DIRECTION_RTL
        val hasQuery = !input.text.isNullOrEmpty()
        if (rtl) {
            input.nextFocusLeftId = if (hasQuery) clear.id else submit.id
            input.nextFocusRightId = submit.id
            clear.nextFocusRightId = input.id
            clear.nextFocusLeftId = submit.id
            submit.nextFocusRightId = if (hasQuery) clear.id else input.id
            submit.nextFocusLeftId = input.id
        } else {
            input.nextFocusRightId = if (hasQuery) clear.id else submit.id
            input.nextFocusLeftId = submit.id
            clear.nextFocusLeftId = input.id
            clear.nextFocusRightId = submit.id
            submit.nextFocusLeftId = if (hasQuery) clear.id else input.id
            submit.nextFocusRightId = input.id
        }
        clear.nextFocusDownId = recycler?.id ?: View.NO_ID
        submit.nextFocusDownId = recycler?.id ?: View.NO_ID

    }

    private fun hideSearchKeyboard(target: View) {
        val imm = requireContext().getSystemService(Context.INPUT_METHOD_SERVICE) as InputMethodManager
        imm.hideSoftInputFromWindow(target.windowToken, 0)
    }

    private fun performLiveSearch() {
        val input = searchInput ?: return
        val query = input.text?.toString()?.trim().orEmpty()
        if (query.isEmpty()) {
            searchJob?.cancel()
            searchApplied = false
            render(baseRows)
            input.requestFocus()
            return
        }

        hideSearchKeyboard(input)
        searchJob?.cancel()
        searchJob = viewLifecycleOwner.lifecycleScope.launch {
            searchSubmit?.isEnabled = false
            try {
                val results = repository.search(query)
                if (!isAdded || searchInput?.text?.toString()?.trim() != query) return@launch
                searchApplied = true
                render(results)
                if (results.isEmpty()) {
                    Toast.makeText(
                        requireContext(),
                        R.string.live_search_empty,
                        Toast.LENGTH_SHORT
                    ).show()
                } else {
                    initialFocusPending = true
                    focusFirstLiveCard()
                }
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                if (!isAdded || searchInput?.text?.toString()?.trim() != query) return@launch
                // Search remains useful offline: filter the locally cached Live cards.
                val localMatches = baseRows.filter { item ->
                    val needle = query.lowercase()
                    item.name.orEmpty().lowercase().contains(needle) ||
                        item.names.orEmpty().values.any { it.lowercase().contains(needle) }
                }
                searchApplied = true
                render(localMatches)
                Toast.makeText(
                    requireContext(),
                    R.string.live_search_failed,
                    Toast.LENGTH_SHORT
                ).show()
                if (localMatches.isNotEmpty()) {
                    initialFocusPending = true
                    focusFirstLiveCard()
                }
            } finally {
                searchSubmit?.isEnabled = true
            }
        }
    }

    /** Refresh the current Live page without replacing its Fragment or Player. */
    fun refreshAfterNetworkRecovery() {
        if (!isAdded || view == null) return
        loadRows()
    }

    override fun requestInitialFocus() {
        initialFocusPending = true
        focusFirstLiveCard()
    }

    /**
     * Wait for the first owner row and its nested horizontal card list to be
     * laid out before requesting focus. This avoids leaving focus on the page
     * container when RecyclerView has not attached its first card yet.
     */
    private fun focusFirstLiveCard() {
        val list = recycler ?: return
        if (list.adapter?.itemCount == 0) return

        list.post {
            if (!isAdded || !initialFocusPending) return@post
            list.setSelectedPosition(0)

            list.post {
                val row = list.findViewHolderForAdapterPosition(0)
                    ?.itemView
                    ?.findViewById<HorizontalGridView>(R.id.liveCardsRecycler)
                    ?: return@post

                row.setSelectedPosition(0)
                row.post {
                    val firstCard = row.getChildAt(0)
                    if (firstCard != null) {
                        initialFocusPending = false
                        firstCard.requestFocus()
                    } else {
                        row.requestFocus()
                    }
                }
            }
        }
    }

    private fun loadRows() {
        loadJob?.cancel()
        loadJob = viewLifecycleOwner.lifecycleScope.launch {
            // Render Room cache immediately; a failed request must not hide it.
            val cached = try {
                repository.getCached()
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                emptyList()
            }
            baseRows = cached
            if (!searchApplied) render(cached)

            try {
                val fresh = repository.refresh()
                baseRows = fresh
                if (!searchApplied) render(fresh)
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                // Keep cached rows visible while offline or when the API fails.
            }
        }
    }

    private fun render(items: List<LiveItem>) {
        val rows = mutableListOf<LiveOwnerRow>()

        // Owner ID 0 and missing owner are the same system-owned section.
        val systemItems = items.filter { it.owner == null || it.owner.id == 0L }
        appendMediaRows(
            rows = rows,
            owner = null,
            items = systemItems,
            systemOwner = true,
            showHeader = true
        )

        val ownerGroups = LinkedHashMap<Long, MutableList<LiveItem>>()
        items.filter { it.owner != null && it.owner.id != 0L }.forEach { item ->
            ownerGroups.getOrPut(item.owner!!.id) { mutableListOf() }.add(item)
        }

        ownerGroups.values.forEach { ownerItems ->
            appendMediaRows(
                rows = rows,
                owner = ownerItems.firstOrNull()?.owner,
                items = ownerItems,
                systemOwner = false,
                showHeader = true
            )
        }

        adapter?.submit(rows)

        if (initialFocusPending && rows.isNotEmpty()) {
            focusFirstLiveCard()
        }
    }

    private fun appendMediaRows(
        rows: MutableList<LiveOwnerRow>,
        owner: ir.hamrahang.tv.data.SourceOwner?,
        items: List<LiveItem>,
        systemOwner: Boolean,
        showHeader: Boolean
    ) {
        val videoItems = items.filter { it.isVideo }
        val audioItems = items.filterNot { it.isVideo }

        if (videoItems.isNotEmpty()) {
            rows += LiveOwnerRow(owner, videoItems, mediaType = MediaType.VIDEO, systemOwner = systemOwner, showHeader = showHeader)
        }
        if (audioItems.isNotEmpty()) {
            rows += LiveOwnerRow(owner, audioItems, mediaType = MediaType.AUDIO, systemOwner = systemOwner, showHeader = showHeader && videoItems.isEmpty())
        }
    }

    private fun playLive(selected: LiveItem, ownerItems: List<LiveItem>) {
        val mediaType = if (selected.isVideo) MediaType.VIDEO else MediaType.AUDIO
        val contentType = if (selected.isVideo) ContentType.VIDEO else ContentType.SONG

        val queueItems = ownerItems
            .filter { it.isVideo == selected.isVideo }
            .mapNotNull { live ->
                val sources = live.sources.filter { it.url.isNotBlank() }
                if (sources.isEmpty()) return@mapNotNull null

                val artwork = live.thumb.takeIf { it.isNotBlank() }
                val media = PlayerMedia(
                    id = "live_${live.id}",
                    title = live.title,
                    artist = live.owner?.name ?: "Live",
                    artwork = artwork,
                    sources = sources,
                    type = mediaType,
                    contentType = contentType
                )
                PlaybackItem(
                    id = media.id ?: return@mapNotNull null,
                    title = live.title,
                    artwork = artwork,
                    artists = emptyList(),
                    contentType = contentType,
                    liveMedia = media
                )
            }
        if (queueItems.isEmpty()) return

        val selectedId = "live_${selected.id}"
        val selectedIndex = queueItems.indexOfFirst { it.id == selectedId }
            .takeIf { it >= 0 } ?: 0
        val ownerName = selected.owner?.name?.takeIf { it.isNotBlank() }
            ?: if (selected.isVideo) "Live Video" else "Live Audio"

        PlaybackManager.start(
            PlaybackSession(
                id = "live_${selected.owner?.id ?: "system"}_${mediaType.name.lowercase()}",
                title = ownerName,
                source = PlaybackSource.LIVE,
                items = queueItems,
                currentIndex = selectedIndex,
                mediaType = mediaType
            )
        )

        if (selected.isVideo) {
            UiNavigator.openFullscreenPlayer()
        } else {
            UiNavigator.openPlayback()
        }
    }

    override fun onDestroyView() {
        loadJob?.cancel()
        searchJob?.cancel()
        searchApplied = false
        searchInput?.setText("")
        searchInput = null
        searchClear = null
        searchSubmit = null
        baseRows = emptyList()
        recycler?.adapter = null
        recycler = null
        adapter = null
        super.onDestroyView()
    }
}
