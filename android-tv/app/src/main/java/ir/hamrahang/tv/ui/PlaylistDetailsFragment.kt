package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.HorizontalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.playlist.PlaylistMedia
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.ui.hero.HeroWidget
import kotlinx.coroutines.launch

class PlaylistDetailsFragment : Fragment(R.layout.fragment_playlist_details) {

    private var playlistId: String? = null
    private lateinit var heroWidget: HeroWidget
    private lateinit var itemsView: HorizontalGridView
    private lateinit var adapter: PlaylistMediaAdapter
    private val repository by lazy { PlaylistRepository(requireContext()) }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        playlistId = arguments?.getString(ARG_PLAYLIST_ID)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        heroWidget = HeroWidget(view.findViewById(R.id.heroContainer), viewLifecycleOwner)
        itemsView = view.findViewById(R.id.playlistItems)
        itemsView.setNumRows(1)
        itemsView.setHorizontalSpacing(0)
        itemsView.setWindowAlignment(BaseGridView.WINDOW_ALIGN_NO_EDGE)
        itemsView.setWindowAlignmentOffsetPercent(50f)
        itemsView.setItemAlignmentOffsetPercent(50f)
        itemsView.setItemAlignmentOffsetWithPadding(true)

        adapter = PlaylistMediaAdapter(
            emptyList(),
            onFocus = ::showMediaHero,
            onClick = ::openMedia
        )
        itemsView.adapter = adapter
        loadPlaylist()
    }

    private fun loadPlaylist() {
        val id = playlistId ?: return
        viewLifecycleOwner.lifecycleScope.launch {
            val playlist = repository.getPlaylist(id) ?: return@launch
            HeroManager.show(HeroContent.PlaylistHero(playlist))
            BackgroundManager.set(playlist.thumbnail)
            val media = repository.getPlaylistContent(id)
            if (!isAdded) return@launch
            adapter.submit(media)
            if (media.isEmpty()) {
                val empty = TextView(requireContext()).apply {
                    text = "This playlist is empty"
                    setTextColor(0xFFFFFFFF.toInt())
                    textSize = 18f
                }
                // Keep the native content row empty; the playlist title remains in the hero.
                itemsView.visibility = View.GONE
                return@launch
            }
            itemsView.visibility = View.VISIBLE
            itemsView.post {
                itemsView.setSelectedPosition(0)
                itemsView.post {
                    itemsView.findViewHolderForAdapterPosition(0)?.itemView?.requestFocus()
                }
            }
        }
    }

    private fun showMediaHero(media: PlaylistMedia) {
        when (media) {
            is PlaylistMedia.SongItem -> {
                BackgroundManager.set(media.value.heroBackground)
                HeroManager.show(HeroContent.SongHero(media.value))
            }
            is PlaylistMedia.PodcastItem -> {
                BackgroundManager.set(media.value.heroBackground)
                HeroManager.show(HeroContent.PodcastHero(media.value))
            }
            is PlaylistMedia.VideoItem -> {
                BackgroundManager.set(media.value.heroBackground)
                HeroManager.show(HeroContent.VideoHero(media.value))
            }
            is PlaylistMedia.MusicVideoItem -> {
                BackgroundManager.set(media.value.heroBackground)
                HeroManager.show(HeroContent.MusicVideoHero(media.value))
            }
        }
    }

    private fun openMedia(media: PlaylistMedia) {
        when (media) {
            is PlaylistMedia.SongItem ->
                UiNavigator.openMediaDetail(ContentType.SONG, media.value.id)
            is PlaylistMedia.PodcastItem ->
                UiNavigator.openMediaDetail(ContentType.PODCAST, media.value.id)
            is PlaylistMedia.VideoItem ->
                UiNavigator.openMediaDetail(ContentType.VIDEO, media.value.id)
            is PlaylistMedia.MusicVideoItem ->
                UiNavigator.openMediaDetail(ContentType.MUSICVIDEO, media.value.id)
        }
    }

    companion object {
        private const val ARG_PLAYLIST_ID = "playlist_id"

        fun newInstance(id: String) = PlaylistDetailsFragment().apply {
            arguments = Bundle().apply { putString(ARG_PLAYLIST_ID, id) }
        }
    }
}
