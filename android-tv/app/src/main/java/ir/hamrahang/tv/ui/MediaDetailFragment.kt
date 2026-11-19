package ir.hamrahang.tv.ui

import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.ImageButton
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.leanback.widget.VerticalGridView
import androidx.leanback.widget.HorizontalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.rows.ContentRows
import ir.hamrahang.tv.ui.rows.ContentRowsAdapter
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.launch

class MediaDetailFragment : Fragment(R.layout.fragment_media_detail) {

    private lateinit var heroWidget: HeroWidget
    private lateinit var rows: VerticalGridView
    private lateinit var actions: View
    private lateinit var playButton: Button
    private lateinit var bookmarkButton: ImageButton
    private lateinit var infoButton: ImageButton
    private lateinit var moreButton: ImageButton
    private lateinit var lyricsView: TextView

    private var contentType: ContentType = ContentType.SONG
    private var contentId: String = ""
    private var album: ir.hamrahang.tv.domain.album.Album? = null
    private var song: ir.hamrahang.tv.domain.song.Song? = null
    private var podcast: ir.hamrahang.tv.domain.podcast.Podcast? = null
    private var video: ir.hamrahang.tv.domain.video.Video? = null
    private var pendingPlaybackSession: PlaybackSession? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        contentType = ContentType.values().firstOrNull {
            it.name == arguments?.getString(ARG_TYPE)
        } ?: ContentType.SONG
        contentId = arguments?.getString(ARG_ID).orEmpty()
        pendingPlaybackSession = UiNavigator.consumePendingDetailSession()
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        heroWidget = HeroWidget(view.findViewById(R.id.heroContainer), viewLifecycleOwner)
        rows = view.findViewById(R.id.detailRows)
        rows.setNumColumns(1)
        rows.setItemAlignmentOffset(0)
        rows.setItemAlignmentOffsetPercent(0f)
        rows.setWindowAlignment(androidx.leanback.widget.BaseGridView.WINDOW_ALIGN_LOW_EDGE)
        rows.setWindowAlignmentOffset(0)
        rows.setWindowAlignmentOffsetPercent(0f)

        actions = view.findViewById(R.id.singleItemActions)
        playButton = view.findViewById(R.id.btnDetailPlay)
        bookmarkButton = view.findViewById(R.id.btnDetailBookmark)
        infoButton = view.findViewById(R.id.btnDetailInfo)
        moreButton = view.findViewById(R.id.btnDetailMore)
        lyricsView = view.findViewById(R.id.detailLyrics)

        playButton.setOnClickListener { playCurrentDetail() }
        infoButton.setOnClickListener { }
        moreButton.setOnClickListener {
            Toast.makeText(requireContext(), "More options will be available here", Toast.LENGTH_SHORT).show()
        }

        loadDetails()
    }

    private fun loadDetails() {
        if (contentId.isBlank()) return
        viewLifecycleOwner.lifecycleScope.launch {
            try {
                when (contentType) {
                    ContentType.ALBUM -> loadAlbum()
                    ContentType.SONG -> loadSong()
                    ContentType.PODCAST -> loadPodcast()
                    ContentType.VIDEO -> loadVideo(false)
                    ContentType.MUSICVIDEO -> loadVideo(true)
                    else -> Unit
                }
            } catch (_: Exception) {
                Toast.makeText(requireContext(), "Unable to load item details", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private suspend fun loadAlbum() {
        val item = AlbumRepository().getAlbum(contentId)
        album = item
        BackgroundManager.set(item.heroBackground)
        HeroManager.show(HeroContent.AlbumHero(item))

        val albumSongs = SongRepository().getByIds(item.songs)
        val data = ContentRows {
            songs(
                items = albumSongs,
                title = null,
                sessionId = item.id,
                sessionTitle = item.displayTitle(),
                sessionSource = PlaybackSource.ALBUM
            )
        }
        rows.adapter = ContentRowsAdapter(
            rows = data,
            onHeroFocus = { },
            onBackgroundChange = { },
            onItemClick = { event ->
                UiActionDispatcher.dispatch(UiNavigator, event)
            },
            onNavigateUp = {
                playButton.requestFocus()
            },
            onNavigateLeft = {
                requireActivity().findViewById<View>(R.id.menuHome)?.requestFocus()
            }
        )
        rows.visibility = View.VISIBLE
        actions.visibility = View.VISIBLE
        playButton.setOnKeyListener { _, keyCode, event ->
            if (event.action == android.view.KeyEvent.ACTION_DOWN &&
                keyCode == android.view.KeyEvent.KEYCODE_DPAD_DOWN
            ) {
                requestAlbumListFocus()
                true
            } else false
        }
        playButton.post { playButton.requestFocus() }
    }

    private suspend fun loadSong() {
        val item = SongRepository().getSong(contentId)
        song = item
        showSingle(HeroContent.SongHero(item), item.heroBackground, item.meta?.lrc)
    }

    private suspend fun loadPodcast() {
        val item = PodcastRepository().getPodcast(contentId)
        podcast = item
        showSingle(HeroContent.PodcastHero(item), item.heroBackground, item.meta?.lrc)
    }

    private suspend fun loadVideo(isMusicVideo: Boolean) {
        val item = if (isMusicVideo) {
            MusicVideoRepository().getMusicVideo(contentId)
        } else {
            VideoRepository().getVideo(contentId)
        }
        video = item
        val hero = if (isMusicVideo) HeroContent.MusicVideoHero(item) else HeroContent.VideoHero(item)
        showSingle(hero, item.heroBackground, item.meta?.lrc)
    }

    private fun showSingle(hero: HeroContent, background: String?, lyrics: String?) {
        BackgroundManager.set(background)
        HeroManager.show(hero)
        rows.visibility = View.GONE
        actions.visibility = View.VISIBLE

        // meta.lrc is a lyrics resource reference in the current data model,
        // not parsed lyric text. Keep the lyrics panel clean until a lyrics renderer exists.
        lyricsView.text = ""
        lyricsView.visibility = if (
            contentType == ContentType.SONG || contentType == ContentType.PODCAST
        ) View.VISIBLE else View.GONE
        playButton.post { playButton.requestFocus() }
    }

    private fun requestAlbumListFocus() {
        rows.post {
            val rowView = rows.findViewHolderForAdapterPosition(0)?.itemView
            val horizontal = rowView?.findViewById<HorizontalGridView>(R.id.horizontalRecycler)
            horizontal?.post {
                horizontal.setSelectedPosition(0)
                horizontal.post {
                    horizontal.getChildAt(0)?.requestFocus() ?: horizontal.requestFocus()
                }
            }
        }
    }

    private fun playCurrentDetail() {
        viewLifecycleOwner.lifecycleScope.launch {
            val rowSession = pendingPlaybackSession
            if (rowSession != null) {
                PlaybackManager.start(rowSession)
            } else {
                when (contentType) {
                    ContentType.ALBUM -> {
                        val item = album ?: return@launch
                        PlaybackManager.startAlbum(item)
                    }
                    ContentType.SONG -> {
                        val item = song ?: return@launch
                        PlaybackManager.start(singleSession(
                            item.id, item.displayTitle(), item.thumb, item.artists,
                            ContentType.SONG, MediaType.AUDIO
                        ))
                    }
                    ContentType.PODCAST -> {
                        val item = podcast ?: return@launch
                        PlaybackManager.start(singleSession(
                            item.id, item.displayTitle(), item.thumb, item.artists,
                            ContentType.PODCAST, MediaType.AUDIO
                        ))
                    }
                    ContentType.VIDEO, ContentType.MUSICVIDEO -> {
                        val item = video ?: return@launch
                        PlaybackManager.start(singleSession(
                            item.id, item.displayTitle(), item.thumb, item.artists,
                            item.contentType, MediaType.VIDEO
                        ))
                    }
                    else -> return@launch
                }
            }
            UiNavigator.openPlayback()
        }
    }

    private fun singleSession(
        id: String,
        title: String,
        artwork: String,
        artists: List<ir.hamrahang.tv.domain.artist.Artist>,
        type: ContentType,
        mediaType: MediaType
    ) = PlaybackSession(
        id = id,
        title = title,
        source = PlaybackSource.HOME,
        items = listOf(
            PlaybackItem(
                id = id,
                title = title,
                artwork = artwork,
                artists = artists,
                contentType = type
            )
        ),
        currentIndex = 0,
        mediaType = mediaType
    )

    companion object {
        private const val ARG_TYPE = "detail_type"
        private const val ARG_ID = "detail_id"

        fun newInstance(type: ContentType, id: String) = MediaDetailFragment().apply {
            arguments = Bundle().apply {
                putString(ARG_TYPE, type.name)
                putString(ARG_ID, id)
            }
        }
    }
}
