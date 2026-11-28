package ir.hamrahang.app.ui.live

import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.ProgressBar
import android.widget.TextView
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.app.R
import ir.hamrahang.app.ui.player.VideoActivity
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.data.live.LiveRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.launch

data class LiveOwnerRow(
    val id: String,
    val ownerName: String,
    val ownerArtwork: String?,
    val mediaType: MediaType,
    val items: List<LiveItem>
)

class LiveFragment : Fragment(R.layout.fragment_live_mobile) {

    private val repository = LiveRepository()
    private var rowsAdapter: LiveRowsAdapter? = null
    private var loadJob: kotlinx.coroutines.Job? = null

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        val recycler = view.findViewById<RecyclerView>(R.id.liveRows)
        val loading = view.findViewById<ProgressBar>(R.id.liveLoading)
        val empty = view.findViewById<View>(R.id.liveEmpty)
        val emptyText = view.findViewById<TextView>(R.id.liveEmptyText)
        val retry = view.findViewById<Button>(R.id.liveRetry)
        val refresh = view.findViewById<TextView>(R.id.liveRefresh)

        rowsAdapter = LiveRowsAdapter(::playLive)
        recycler.layoutManager = LinearLayoutManager(requireContext())
        recycler.adapter = rowsAdapter

        retry.setOnClickListener { loadRows(forceRefresh = true) }
        refresh.setOnClickListener { loadRows(forceRefresh = true) }

        loadRows(forceRefresh = false, loading = loading, empty = empty, emptyText = emptyText)
    }

    private fun loadRows(
        forceRefresh: Boolean,
        loading: ProgressBar? = view?.findViewById(R.id.liveLoading),
        empty: View? = view?.findViewById(R.id.liveEmpty),
        emptyText: TextView? = view?.findViewById(R.id.liveEmptyText)
    ) {
        loadJob?.cancel()
        loadJob = viewLifecycleOwner.lifecycleScope.launch {
            loading?.isVisible = rowsAdapter?.itemCount == 0
            empty?.isVisible = false

            try {
                if (!forceRefresh) {
                    val cached = repository.getCached()
                    render(cached)
                    loading?.isVisible = false
                }
                val fresh = repository.refresh()
                render(fresh)
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                val cached = runCatching { repository.getCached() }.getOrDefault(emptyList())
                render(cached)
                if (cached.isEmpty()) {
                    emptyText?.setText(R.string.live_load_failed)
                }
            } finally {
                loading?.isVisible = false
                empty?.isVisible = rowsAdapter?.itemCount == 0
            }
        }
    }

    private fun render(items: List<LiveItem>) {
        val rows = mutableListOf<LiveOwnerRow>()
        val systemItems = mutableListOf<LiveItem>()
        val owners = LinkedHashMap<Long, MutableList<LiveItem>>()

        items.forEach { item ->
            val owner = item.owner
            if (owner == null || owner.id == 0L) {
                systemItems += item
            } else {
                owners.getOrPut(owner.id) { mutableListOf() }.add(item)
            }
        }

        appendOwnerRows(rows, "system", getString(R.string.live_hamrahang), null, systemItems)
        owners.values.forEach { ownerItems ->
            val owner = ownerItems.firstOrNull()?.owner
            appendOwnerRows(
                rows,
                owner?.id?.toString() ?: "unknown",
                owner?.name?.takeIf { it.isNotBlank() } ?: getString(R.string.live_creator),
                owner?.meta?.tmb ?: owner?.meta?.ico ?: owner?.meta?.photo,
                ownerItems
            )
        }
        rowsAdapter?.submit(rows)
    }

    private fun appendOwnerRows(
        rows: MutableList<LiveOwnerRow>,
        ownerId: String,
        ownerName: String,
        ownerArtwork: String?,
        items: List<LiveItem>
    ) {
        val videos = items.filter { it.isVideo }
        val audios = items.filterNot { it.isVideo }
        if (videos.isNotEmpty()) {
            rows += LiveOwnerRow("${ownerId}_video", ownerName, ownerArtwork, MediaType.VIDEO, videos)
        }
        if (audios.isNotEmpty()) {
            rows += LiveOwnerRow("${ownerId}_audio", ownerName, ownerArtwork, MediaType.AUDIO, audios)
        }
    }

    private fun playLive(selected: LiveItem, ownerItems: List<LiveItem>) {
        val mediaType = if (selected.isVideo) MediaType.VIDEO else MediaType.AUDIO
        val queueItems = ownerItems.filter { it.isVideo == selected.isVideo }.mapNotNull { live ->
            val sources = live.sources.filter { it.url.isNotBlank() }
            if (sources.isEmpty()) return@mapNotNull null
            val artwork = live.thumb.takeIf { it.isNotBlank() }
            val media = PlayerMedia(
                id = "live_${live.id}",
                title = live.title,
                artist = live.owner?.name ?: getString(R.string.live_title),
                artwork = artwork,
                sources = sources,
                type = mediaType,
                contentType = ContentType.LIVE
            )
            PlaybackItem(
                id = media.id ?: return@mapNotNull null,
                title = live.title,
                artwork = artwork,
                artists = emptyList(),
                contentType = ContentType.LIVE,
                liveMedia = media,
                publicId = live.id
            )
        }
        if (queueItems.isEmpty()) return

        val selectedId = "live_${selected.id}"
        val selectedIndex = queueItems.indexOfFirst { it.id == selectedId }.coerceAtLeast(0)
        val ownerName = selected.owner?.name?.takeIf { it.isNotBlank() }
            ?: getString(if (selected.isVideo) R.string.live_video else R.string.live_audio)

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
            startActivity(android.content.Intent(requireContext(), VideoActivity::class.java))
        }
    }

    override fun onDestroyView() {
        loadJob?.cancel()
        rowsAdapter = null
        super.onDestroyView()
    }
}
