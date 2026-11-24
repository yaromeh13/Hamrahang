package ir.hamrahang.tv.ui

import android.net.Uri
import android.os.Bundle
import android.view.View
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.VerticalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.data.live.LiveRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerFragment
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerMedia
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.launch

class LiveFragment : Fragment(R.layout.fragment_live) {

    private val repository = LiveRepository()
    private var adapter: LiveRowsAdapter? = null
    private var recycler: VerticalGridView? = null
    private var loadJob: kotlinx.coroutines.Job? = null

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        recycler = view.findViewById(R.id.liveRowsRecycler)
        recycler?.apply {
            setNumColumns(1)
            setItemAlignmentOffset(0)
            setItemAlignmentOffsetPercent(0f)
            setWindowAlignment(BaseGridView.WINDOW_ALIGN_NO_EDGE)
            windowAlignment = VerticalGridView.WINDOW_ALIGN_NO_EDGE
            clipToPadding = false
        }

        adapter = LiveRowsAdapter(emptyList(), ::playLive)
        recycler?.adapter = adapter

        loadRows()
    }

    /** Refresh the current Live page without replacing its Fragment or Player. */
    fun refreshAfterNetworkRecovery() {
        if (!isAdded || view == null) return
        loadRows()
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

        if (items.isNotEmpty()) {
            recycler?.post {
                if (recycler?.hasFocus() != true) {
                    recycler?.findViewHolderForAdapterPosition(0)
                        ?.itemView
                        ?.findViewById<androidx.leanback.widget.HorizontalGridView>(R.id.liveCardsRecycler)
                        ?.let { row ->
                            row.post {
                                row.setSelectedPosition(0)
                                row.getChildAt(0)?.requestFocus()
                            }
                        }
                }
            }
        }
    }

    private fun playLive(item: LiveItem) {
        val playableSources = item.sources.mapNotNull { source ->
            val rawUrl = source.url.trim()
            if (rawUrl.isEmpty()) return@mapNotNull null

            // A fresh dt value is added at selection time, preserving any
            // existing query parameters on the stream URL.
            val urlWithTimestamp = Uri.parse(rawUrl)
                .buildUpon()
                .appendQueryParameter("dt", System.currentTimeMillis().toString())
                .build()
                .toString()

            source.copy(url = urlWithTimestamp)
        }

        if (playableSources.isEmpty()) return

        val media = PlayerMedia(
            id = "live_${item.id}",
            title = item.title,
            artist = item.owner?.name ?: "Live",
            artwork = item.thumb.takeIf { it.isNotBlank() },
            sources = playableSources,
            type = MediaType.AUDIO,
            contentType = ContentType.SONG
        )

        PlayerManager.play(requireContext(), media)
        (activity as? ir.hamrahang.tv.MainActivity)
            ?.openFullscreenPlayer(PlayerFragment())
    }

    override fun onDestroyView() {
        loadJob?.cancel()
        recycler?.adapter = null
        recycler = null
        adapter = null
        super.onDestroyView()
    }
}
