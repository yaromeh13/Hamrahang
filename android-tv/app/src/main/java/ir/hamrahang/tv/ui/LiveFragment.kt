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

                // Clearing the query (by the X button or Backspace) restores
                // the original Live rows without another network request.
                if (!hasText && searchApplied) {
                    loadJob?.cancel()
                    searchApplied = false
                    render(baseRows)
                }
            }

            override fun afterTextChanged(s: android.text.Editable?) = Unit
        })

        clear.setOnClickListener {
            loadJob?.cancel()
            input.setText("")
            input.requestFocus()
            hideSearchKeyboard(input)
        }

        submit.setOnClickListener { performLiveSearch() }

        input.setOnEditorActionListener { _, actionId, event ->
            val enterPressed = event?.keyCode == KeyEvent.KEYCODE_ENTER &&
                event.action == KeyEvent.ACTION_DOWN
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
            searchApplied = false
            render(baseRows)
            input.requestFocus()
            return
        }

        hideSearchKeyboard(input)
        loadJob?.cancel()
        loadJob = viewLifecycleOwner.lifecycleScope.launch {
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
            val cached = runCatching { repository.getCached() }.getOrDefault(emptyList())
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
        val grouped = LinkedHashMap<Long, MutableList<LiveItem>>()
        val owners = LinkedHashMap<Long, ir.hamrahang.tv.data.SourceOwner?>()

        items.forEach { item ->
            val owner = item.owner
            val ownerId = owner?.id ?: Long.MIN_VALUE
            grouped.getOrPut(ownerId) { mutableListOf() }.add(item)
            if (!owners.containsKey(ownerId)) owners[ownerId] = owner
        }

        val rows = grouped.map { (ownerId, liveItems) ->
            val owner = owners[ownerId]
            val featured = isHamrahangOwner(owner)
            LiveOwnerRow(
                owner = owner,
                items = liveItems,
                featured = featured
            )
        }.filter { it.items.isNotEmpty() }
            .sortedByDescending { it.featured }

        adapter?.submit(rows)

        if (initialFocusPending && items.isNotEmpty()) {
            focusFirstLiveCard()
        }
    }

    private fun isHamrahangOwner(owner: ir.hamrahang.tv.data.SourceOwner?): Boolean {
        if (owner == null) return false

        val name = owner.name?.trim()?.lowercase().orEmpty()
        if (name == "hamrahang" || name == "همراهانگ") return true

        val ownerUrl = owner.meta?.url?.trim()?.lowercase().orEmpty()
        return ownerUrl.contains("hamrahang.ir")
    }

    private fun playLive(selected: LiveItem, ownerItems: List<LiveItem>) {
        val queueItems = ownerItems.mapNotNull { live ->
            val sources = live.sources.filter { it.url.isNotBlank() }
            if (sources.isEmpty()) return@mapNotNull null

            val artwork = live.thumb.takeIf { it.isNotBlank() }
            val media = PlayerMedia(
                id = "live_${live.id}",
                title = live.title,
                artist = live.owner?.name ?: "Live",
                artwork = artwork,
                sources = sources,
                type = MediaType.AUDIO,
                contentType = ContentType.SONG
            )
            PlaybackItem(
                id = media.id ?: return@mapNotNull null,
                title = live.title,
                artwork = artwork,
                artists = emptyList(),
                contentType = ContentType.SONG,
                liveMedia = media
            )
        }
        if (queueItems.isEmpty()) return

        val selectedId = "live_${selected.id}"
        val selectedIndex = queueItems.indexOfFirst { it.id == selectedId }
            .takeIf { it >= 0 } ?: 0
        val ownerName = selected.owner?.name?.takeIf { it.isNotBlank() } ?: "Live"

        // The queue contains only stations from the selected Owner row.
        PlaybackManager.start(
            PlaybackSession(
                id = "live_owner_${selected.owner?.id ?: ownerName}",
                title = ownerName,
                source = PlaybackSource.LIVE,
                items = queueItems,
                currentIndex = selectedIndex,
                mediaType = MediaType.AUDIO
            )
        )
        UiNavigator.openPlayback()
    }

    override fun onDestroyView() {
        loadJob?.cancel()
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
