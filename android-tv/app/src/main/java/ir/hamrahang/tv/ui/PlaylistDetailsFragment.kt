package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.widget.Button
import android.widget.ImageButton
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.HorizontalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.playlist.PlaylistMedia
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.message.MessageManager
import kotlinx.coroutines.launch

class PlaylistDetailsFragment : Fragment(R.layout.fragment_playlist_details) {

    private var playlistId: String? = null
    private lateinit var heroWidget: HeroWidget
    private lateinit var itemsView: HorizontalGridView
    private lateinit var emptyState: View
    private lateinit var playButton: Button
    private lateinit var bookmarkButton: ImageButton
    private lateinit var moreButton: ImageButton
    private lateinit var infoButton: ImageButton
    private lateinit var adapter: PlaylistMediaAdapter
    private var playlist: PlaylistEntity? = null
    private var mediaItems: List<PlaylistMedia> = emptyList()
    private val repository by lazy { PlaylistRepository(requireContext()) }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        playlistId = arguments?.getString(ARG_PLAYLIST_ID)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        heroWidget = HeroWidget(view.findViewById(R.id.heroContainer), viewLifecycleOwner)
        itemsView = view.findViewById(R.id.playlistItems)
        emptyState = view.findViewById(R.id.playlistEmpty)
        playButton = view.findViewById(R.id.btnDetailPlay)
        bookmarkButton = view.findViewById(R.id.btnDetailBookmark)
        moreButton = view.findViewById(R.id.btnDetailMore)
        infoButton = view.findViewById(R.id.btnDetailInfo)

        itemsView.setNumRows(1)
        itemsView.setHorizontalSpacing(0)

        // Match the edge alignment used by the other content rows.
        val edgePadding = resources.getDimensionPixelSize(R.dimen.content_edge_spacing)
        itemsView.setPaddingRelative(
            itemsView.paddingStart,
            itemsView.paddingTop,
            edgePadding,
            itemsView.paddingBottom
        )
        itemsView.setWindowAlignment(BaseGridView.WINDOW_ALIGN_BOTH_EDGE)
        itemsView.setWindowAlignmentOffset(0)
        itemsView.setWindowAlignmentOffsetPercent(50f)
        itemsView.setItemAlignmentOffset(0)
        itemsView.setItemAlignmentOffsetPercent(50f)

        adapter = PlaylistMediaAdapter(
            emptyList(),
            onFocus = ::showMediaHero,
            onClick = ::openMedia
        )
        itemsView.adapter = adapter

        playButton.setOnClickListener { playPlaylist() }
        bookmarkButton.setOnClickListener { toggleBookmark() }
        moreButton.setOnClickListener { showMoreOptions() }
        infoButton.setOnClickListener {
            MessageManager.info("Playlist information: ${playlist?.itemCount ?: 0} items")
        }
        playButton.setOnKeyListener { _, keyCode, event ->
            if (event.action == KeyEvent.ACTION_DOWN && keyCode == KeyEvent.KEYCODE_DPAD_DOWN) {
                requestFirstItemFocus()
                true
            } else false
        }
        loadPlaylist()
    }

    private fun loadPlaylist() {
        val id = playlistId ?: return
        viewLifecycleOwner.lifecycleScope.launch {
            val loadedPlaylist = repository.getPlaylist(id) ?: return@launch
            playlist = loadedPlaylist
            HeroManager.show(HeroContent.PlaylistHero(loadedPlaylist))
            BackgroundManager.set(loadedPlaylist.thumbnail)
            bindBookmarkState(loadedPlaylist)

            val media = repository.getPlaylistContent(id)
            if (!isAdded) return@launch
            mediaItems = media
            adapter.submit(media)

            // Show the playlist name and its loaded item count on this screen only.
            val playLabel = getString(R.string.play) +
                " " + loadedPlaylist.name + " - " + media.size
            playButton.text = playLabel
            playButton.contentDescription = playLabel

            if (media.isEmpty()) {
                itemsView.visibility = View.GONE
                emptyState.visibility = View.VISIBLE
                playButton.isEnabled = false
                return@launch
            }
            playButton.isEnabled = true
            emptyState.visibility = View.GONE
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

    private fun requestFirstItemFocus() {
        itemsView.post {
            itemsView.setSelectedPosition(0)
            itemsView.post {
                itemsView.findViewHolderForAdapterPosition(0)?.itemView?.requestFocus()
            }
        }
    }

    private fun bindBookmarkState(value: PlaylistEntity) {
        val id = value.remoteId?.toLongOrNull() ?: return
        viewLifecycleOwner.lifecycleScope.launch {
            val saved = FavoriteManager.isFavorite(id, ContentType.PLAYLIST)
            bookmarkButton.isSelected = saved
            bookmarkButton.setImageResource(
                if (saved) R.drawable.ic_bookmark else R.drawable.ic_bookmark_outline
            )
        }
    }

    private fun toggleBookmark() {
        val id = playlist?.remoteId?.toLongOrNull()
        if (id == null) {
            MessageManager.info("This local playlist is not linked to an account yet")
            return
        }
        viewLifecycleOwner.lifecycleScope.launch {
            val saved = FavoriteManager.toggle(id, ContentType.PLAYLIST)
            bookmarkButton.isSelected = saved
            bookmarkButton.setImageResource(
                if (saved) R.drawable.ic_bookmark else R.drawable.ic_bookmark_outline
            )
            MessageManager.info(if (saved) "Playlist bookmarked" else "Playlist removed from bookmarks")
        }
    }

    private fun showMoreOptions() {
        val value = playlist ?: return
        MoreOptionsPanel.show(
            this,
            PlaybackItem(
                id = value.remoteId ?: value.localId,
                title = value.name,
                artwork = value.thumbnail,
                artists = emptyList(),
                contentType = ContentType.PLAYLIST
            ),
            moreButton
        )
    }

    private fun playPlaylist() {
        val value = playlist ?: return
        if (mediaItems.isEmpty()) return
        val queue = mediaItems.mapNotNull { media ->
            when (media) {
                is PlaylistMedia.SongItem -> PlaybackItem(
                    media.value.id, media.value.title, media.value.thumb,
                    media.value.artists, ContentType.SONG, media.value.meta?.ncp
                )
                is PlaylistMedia.PodcastItem -> PlaybackItem(
                    media.value.id, media.value.title, media.value.thumb,
                    media.value.artists, ContentType.PODCAST, media.value.meta?.ncp
                )
                is PlaylistMedia.VideoItem -> PlaybackItem(
                    media.value.id, media.value.title, media.value.thumb,
                    media.value.artists, ContentType.VIDEO, media.value.meta?.ncp
                )
                is PlaylistMedia.MusicVideoItem -> PlaybackItem(
                    media.value.id, media.value.title, media.value.thumb,
                    media.value.artists, ContentType.MUSICVIDEO, media.value.meta?.ncp
                )
            }
        }
        if (queue.isEmpty()) return
        val firstType = queue.first().contentType
        val mediaType = if (firstType == ContentType.VIDEO || firstType == ContentType.MUSICVIDEO) {
            MediaType.VIDEO
        } else {
            MediaType.AUDIO
        }
        viewLifecycleOwner.lifecycleScope.launch {
            PlaybackManager.start(
                PlaybackSession(
                    id = value.remoteId ?: value.localId,
                    title = value.name,
                    source = PlaybackSource.PLAYLIST,
                    items = queue,
                    currentIndex = 0,
                    mediaType = mediaType
                )
            )
            UiNavigator.openPlayback()
        }
    }

    companion object {
        private const val ARG_PLAYLIST_ID = "playlist_id"

        fun newInstance(id: String) = PlaylistDetailsFragment().apply {
            arguments = Bundle().apply { putString(ARG_PLAYLIST_ID, id) }
        }
    }
}
