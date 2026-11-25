package ir.hamrahang.tv.ui

import android.net.Uri
import android.os.Bundle
import android.view.View
import androidx.fragment.app.Fragment
import ir.hamrahang.tv.contract.Focusable
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.HorizontalGridView
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.data.live.LiveRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.player.PlayerMedia
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.launch

class LiveFragment : Fragment(R.layout.fragment_live), Focusable {

    private val repository = LiveRepository()
    private var adapter: LiveRowsAdapter? = null
    private var recycler: VerticalGridView? = null
    private var loadJob: kotlinx.coroutines.Job? = null
    private var initialFocusPending = false

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        recycler = view.findViewById(R.id.liveRowsRecycler)
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
            onItemFocus = { item -> BackgroundManager.set(item.thumb.takeIf { it.isNotBlank() }) }
        )
        recycler?.adapter = adapter

        loadRows()
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
            render(cached)

            try {
                val fresh = repository.refresh()
                render(fresh)
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

        adapter?.submit(
            grouped.map { (ownerId, liveItems) ->
                LiveOwnerRow(
                    owner = owners[ownerId],
                    items = liveItems
                )
            }.filter { it.items.isNotEmpty() }
        )

        if (initialFocusPending && items.isNotEmpty()) {
            focusFirstLiveCard()
        }
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
        recycler?.adapter = null
        recycler = null
        adapter = null
        super.onDestroyView()
    }
}
