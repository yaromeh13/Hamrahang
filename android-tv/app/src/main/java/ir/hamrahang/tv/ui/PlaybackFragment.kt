package ir.hamrahang.tv.playback

import android.os.Bundle
import android.util.Log
import android.view.View
import android.widget.ImageButton
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.SeekBar
import android.widget.TextView
import android.widget.FrameLayout
import androidx.activity.OnBackPressedCallback
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.media3.common.Player
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import com.google.android.material.imageview.ShapeableImageView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.player.PlaybackInfoState
import ir.hamrahang.tv.player.PlaybackState
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.ui.UiNavigator
import ir.hamrahang.tv.ui.MoreOptionsPanel
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.lyrics.LyricsAdapter
import ir.hamrahang.tv.lyrics.LyricsAutoScroller
import ir.hamrahang.tv.lyrics.LyricsEngine
import ir.hamrahang.tv.lyrics.LyricsParser
import ir.hamrahang.tv.lyrics.LyricsScope
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.launch

class PlaybackFragment : Fragment(R.layout.fragment_playback) {
    private lateinit var imgCover: ImageView
    private lateinit var txtTitle: TextView
    private lateinit var txtAlbum: TextView
    private lateinit var layoutArtists: LinearLayout
    private lateinit var layoutMeta: LinearLayout

    // لیست آهنگ‌ها
    private lateinit var playlist: RecyclerView
    private lateinit var QueueAdapter: PlaybackQueueAdapter
    private lateinit var seekBar: SeekBar
    private lateinit var txtTime: TextView
    private lateinit var btnShuffle: ImageButton
    private lateinit var btnRepeat: ImageButton
    private lateinit var btnPlayPause: ImageButton
    private lateinit var btnNext: ImageButton
    private lateinit var btnPrev: ImageButton
    private lateinit var btnLyrics: ImageButton
    private lateinit var btnPlaylist: ImageButton
    private lateinit var btnFullscreen: ImageButton
    private lateinit var btnBookmark: ImageButton
    private lateinit var btnMore: ImageButton

    private lateinit var lyricsContainer: View
    private lateinit var txtNoLyrics: TextView
    private lateinit var lyricsAdapter: LyricsAdapter
    private lateinit var lyricsRecycler: RecyclerView
    private val lyricsEngine = LyricsEngine()
    private val miniLyricsPreferences = LyricsEngine.defaultPreferences(LyricsScope.MINI)
    private var miniLyricsBody: String? = null
    private var miniLyricsActiveIndex = Int.MIN_VALUE

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        seekBar = view.findViewById(R.id.seekBar)
        txtTime = view.findViewById(R.id.txtTime)
        btnShuffle = view.findViewById(R.id.btnShuffle)
        btnRepeat = view.findViewById(R.id.btnRepeat)
        btnPlayPause = view.findViewById(R.id.btnPlayPause)
        btnNext = view.findViewById(R.id.btnNext)
        btnPrev = view.findViewById(R.id.btnPrev)
        btnLyrics = view.findViewById(R.id.btnLyrics)
        btnPlaylist = view.findViewById(R.id.btnPlaylist)
        btnFullscreen = view.findViewById(R.id.btnFullscreen)
        btnBookmark = view.findViewById(R.id.btnBookmark)
        btnMore = view.findViewById(R.id.btnMore)
        imgCover = view.findViewById(R.id.imgCover)
        txtTitle = view.findViewById(R.id.txtTitle)
        txtAlbum = view.findViewById(R.id.txtAlbum)
        layoutArtists = view.findViewById(R.id.layoutArtists)
        layoutMeta = view.findViewById(R.id.layoutMeta)
        playlist = view.findViewById(R.id.listQueue)
        lyricsContainer = view.findViewById(R.id.lyricsContainer)
        txtNoLyrics = view.findViewById(R.id.txtNoLyrics)

        lyricsAdapter = LyricsAdapter(requireContext())
        lyricsRecycler = RecyclerView(requireContext()).apply {
            layoutManager = LinearLayoutManager(requireContext())
            adapter = lyricsAdapter
            itemAnimator = null
            overScrollMode = View.OVER_SCROLL_NEVER
            isFocusable = false
            isFocusableInTouchMode = false
        }
        (lyricsContainer as FrameLayout).addView(
            lyricsRecycler,
            FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT
            )
        )

        playlist.layoutManager = LinearLayoutManager(requireContext())
        val reorderBackCallback = object : OnBackPressedCallback(false) {
            override fun handleOnBackPressed() {
                QueueAdapter.cancelReorder()
            }
        }
        requireActivity().onBackPressedDispatcher.addCallback(viewLifecycleOwner, reorderBackCallback)

        QueueAdapter = PlaybackQueueAdapter(
            onClick = { PlaybackManager.play(it) },
            onRemove = { PlaybackManager.remove(it) },
            onMore = { item -> MoreOptionsPanel.show(this, item) },
            onReorderCommit = { PlaybackManager.reorder(it) },
            onReorderStateChanged = { reorderBackCallback.isEnabled = it }
        )
        playlist.adapter = QueueAdapter

        seekBar.setOnSeekBarChangeListener(object :
            SeekBar.OnSeekBarChangeListener {

            override fun onProgressChanged(
                seekBar: SeekBar?,
                progress: Int,
                fromUser: Boolean
            ) {

                if (fromUser) {
                    PlayerManager.seekTo(progress.toLong())
                }
            }
            override fun onStartTrackingTouch(seekBar: SeekBar?) {}
            override fun onStopTrackingTouch(seekBar: SeekBar?) {}
        })

        observePlayerUi()
        observePlaybackInfo()
        observeSession()

        btnPlayPause.setOnClickListener {PlayerManager.toggle()}
        btnNext.setOnClickListener {PlaybackManager.next()}
        btnPrev.setOnClickListener {PlaybackManager.previous()}
        btnFullscreen.setOnClickListener {UiNavigator.openFullscreenPlayer()}
        btnBookmark.setOnClickListener {
            val item = currentPlaybackItem()
            val mediaId = item?.let { (it.publicId ?: it.id).toLongOrNull() }
            if (item == null || mediaId == null) {
                MessageManager.info("This item cannot be bookmarked")
            } else {
                viewLifecycleOwner.lifecycleScope.launch {
                    val saved = FavoriteManager.toggle(mediaId, item.contentType)
                    btnBookmark.isSelected = saved
                    btnBookmark.alpha = if (saved) 1f else 0.65f
                    MessageManager.info(if (saved) "Added to bookmarks" else "Removed from bookmarks")
                }
            }
        }
        btnMore.setOnClickListener {
            currentPlaybackItem()?.let { MoreOptionsPanel.show(this, it, btnMore) }
                ?: MessageManager.info("No current media")
        }
        btnPlayPause.post {btnPlayPause.requestFocus()}
        view.post {btnPlayPause.requestFocus()}
        btnShuffle.setOnClickListener {PlayerManager.toggleShuffle()}
        btnRepeat.setOnClickListener {PlayerManager.toggleRepeat()}
        btnLyrics.setOnClickListener {
            val show = !PlayerState.uiState.value.lyricsVisible
            PlayerState.setLyricsVisible(show)
            if (show) {
                PlayerState.setPlaylistVisible(false)
            }
        }
        btnPlaylist.setOnClickListener {
            val show = !PlayerState.uiState.value.playlistVisible
            PlayerState.setPlaylistVisible(show)
            if (show) {
                PlayerState.setLyricsVisible(false)
            }
        }
    }


    private fun observePlayerUi() {
        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.uiState.collectLatest { ui ->
                when (ui.playbackState) {
                    PlaybackState.LOADING -> {
                        btnPlayPause.isEnabled = false
                        btnPlayPause.setImageResource(R.drawable.ic_loading)
                    }

                    PlaybackState.PLAYING -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_pause)
                    }

                    PlaybackState.PAUSED -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_play_arrow)
                    }

                    PlaybackState.ERROR -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_error)
                    }

                    PlaybackState.IDLE -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_play_arrow)
                    }
                }



                val isLive = currentPlaybackItem()?.liveMedia != null
                if (isLive) {
                    // Live has no finite timeline: keep the bar visible,
                    // but clear any progress/time left by the previous item.
                    seekBar.max = 1
                    if (!seekBar.isPressed) {
                        seekBar.progress = 0
                    }
                    txtTime.text = "LIVE"
                } else if (ui.duration > 0L) {
                    seekBar.max = ui.duration.toInt()
                    if (!seekBar.isPressed) {
                        seekBar.progress = ui.position.toInt()
                    }
                    txtTime.text = "${format(ui.position)} / ${format(ui.duration)}"
                } else {
                    // Avoid showing stale values while finite media is loading.
                    seekBar.max = 1
                    if (!seekBar.isPressed) {
                        seekBar.progress = 0
                    }
                    txtTime.text = "00:00 / --:--"
                }

                btnShuffle.alpha =
                    if (ui.shuffle)
                        1f
                    else
                        0.5f

                btnRepeat.alpha =
                    if (ui.repeatMode == Player.REPEAT_MODE_OFF)
                        0.5f
                    else
                        1f
                btnRepeat.setImageResource(
                    if (ui.repeatMode == Player.REPEAT_MODE_ONE)
                        R.drawable.ic_repeat_one
                    else
                        R.drawable.ic_repeat
                )
                playlist.visibility =
                    if (ui.playlistVisible)
                        View.VISIBLE
                    else
                        View.GONE

                lyricsContainer.visibility =
                    if (ui.lyricsVisible)
                        View.VISIBLE
                    else
                        View.GONE

                bindMiniLyrics(PlayerState.playbackInfo.value, ui.position)
            }

        }

    }
    private fun format(ms: Long): String {
        val totalSeconds = ms / 1000
        val minutes = totalSeconds / 60
        val seconds = totalSeconds % 60
        return String.format("%02d:%02d", minutes, seconds)
    }
    private fun observeSession() {
        viewLifecycleOwner.lifecycleScope.launch {
            PlaybackManager.session.collectLatest { session ->
                session ?: return@collectLatest
                QueueAdapter.submit(
                    session.items,
                    session.currentIndex
                )
                session.items.getOrNull(session.currentIndex)?.let(::bindBookmarkState)
                playlist.post {
                    val lm = playlist.layoutManager as? LinearLayoutManager
                    val first = lm?.findFirstVisibleItemPosition() ?: RecyclerView.NO_POSITION
                    val last = lm?.findLastVisibleItemPosition() ?: RecyclerView.NO_POSITION
                    if (session.currentIndex !in first..last) {
                        playlist.smoothScrollToPosition(session.currentIndex)
                    }
                }
            }
        }
    }
    private fun currentPlaybackItem() =
        PlaybackManager.session.value?.let { session ->
            session.items.getOrNull(session.currentIndex)
        }

    private fun bindBookmarkState(item: PlaybackItem) {
        val mediaId = (item.publicId ?: item.id).toLongOrNull()
        if (mediaId == null) {
            btnBookmark.isEnabled = false
            btnBookmark.alpha = 0.35f
            return
        }
        btnBookmark.isEnabled = true
        viewLifecycleOwner.lifecycleScope.launch {
            val saved = FavoriteManager.isFavorite(mediaId, item.contentType)
            if (::btnBookmark.isInitialized) {
                btnBookmark.isSelected = saved
                btnBookmark.alpha = if (saved) 1f else 0.65f
            }
        }
    }

    private fun observePlaybackInfo() {
        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.playbackInfo.collectLatest { info ->
                txtTitle.text = info.title
                bindMiniLyrics(info, PlayerState.uiState.value.position)
                bindArtists(info.artists)
                txtAlbum.text = ""
                txtAlbum.visibility = View.GONE
                    /*if (info.metadata.albumName.isNullOrBlank())
                        View.GONE
                    else
                        View.VISIBLE*/
                bindMeta(info)
                Glide.with(this@PlaybackFragment)
                    .load(info.artwork)
                    .transform(CenterCrop(), RoundedCorners(24))
                    .into(imgCover)
            }
        }
    }
    private fun bindMiniLyrics(info: PlaybackInfoState, positionMs: Long) {
        val body = LyricsParser.selectEntry(
            info.lyricsEntries,
            miniLyricsPreferences.language
        )?.body ?: info.lyrics

        if (body != miniLyricsBody) {
            miniLyricsBody = body
            lyricsEngine.load(body)
            miniLyricsActiveIndex = Int.MIN_VALUE
        }

        val hasLyrics = !body.isNullOrBlank()
        txtNoLyrics.visibility = if (hasLyrics) View.GONE else View.VISIBLE
        lyricsRecycler.visibility = if (hasLyrics) View.VISIBLE else View.GONE

        val frame = lyricsEngine.frame(
            positionMs = positionMs,
            preferences = miniLyricsPreferences,
            scope = LyricsScope.MINI
        )
        val parsed = lyricsEngine.current()
        lyricsAdapter.submit(frame, miniLyricsPreferences, parsed.hasTimedLines)

        if (frame.activeIndex != miniLyricsActiveIndex) {
            miniLyricsActiveIndex = frame.activeIndex
            if (frame.activeIndex >= 0 && lyricsRecycler.isAttachedToWindow) {
                LyricsAutoScroller.centerOn(lyricsRecycler, frame.activeIndex)
            }
        }
    }

    private fun bindArtists(artists: List<Artist>) {
        layoutArtists.removeAllViews()
        val inflater = layoutInflater
        artists.forEach { artist ->
            val item = inflater.inflate(
                R.layout.item_artist_chip,
                layoutArtists,
                false
            )
            val img = item.findViewById<ShapeableImageView>(R.id.imgArtist)
            val txt = item.findViewById<TextView>(R.id.txtArtist)
            txt.text = artist.displayTitle()
            Glide.with(this)
                .load(artist.thumb)
                .circleCrop()
                .into(img)
            item.setOnClickListener { UiNavigator.openArtist(artist) }
            layoutArtists.addView(item)
        }
    }
    private fun bindMeta(info: PlaybackInfoState) {
        layoutMeta.removeAllViews()
        val metadata = info.metadata
        val inflater = layoutInflater
        val items = ArrayList<MetaItem>()
        metadata?.rls
            ?.takeIf { it.isNotBlank() }
            ?.let {
                items += MetaItem(
                    R.drawable.ic_calendar,
                    it
                )
            }
        metadata?.ncp
            ?.takeIf { it > 0 }
            ?.let {
                items += MetaItem(
                    R.drawable.ic_play_arrow,
                    formatCount(it)
                )
            }

        items.forEach { item ->
            val view = inflater.inflate(
                R.layout.item_meta_chip,
                layoutMeta,
                false
            )
            view.findViewById<ImageView>(R.id.imgIcon).setImageResource(item.icon)
            view.findViewById<TextView>(R.id.txtValue).text = item.value
            layoutMeta.addView(view)
        }
    }
    data class MetaItem(
        val icon: Int,
        val value: String
    )
 /*   private fun bindMeta(

        song: Song

    ) {

        layoutMeta.removeAllViews()

        val meta = song.meta ?: return

        val inflater = layoutInflater

        val items = mutableListOf<MetaItem>()

        //----------------------------------
        // Release
        //----------------------------------

        meta.rd?.takeIf { it.isNotBlank() }?.let {

            items += MetaItem(

                R.drawable.ic_calendar,

                it

            )

        }

        //----------------------------------
        // Plays
        //----------------------------------

        meta.ncp?.takeIf { it > 0 }?.let {

            items += MetaItem(

                R.drawable.ic_visibility,

                formatCount(it)

            )

        }

        //----------------------------------
        // Bitrate
        //----------------------------------

        song.sources.firstOrNull()?.bitrate?.let {

            items += MetaItem(

                R.drawable.ic_high_quality,

                "${it}kbps"

            )

        }

        //----------------------------------

        items.forEach { item ->

            val view = inflater.inflate(

                R.layout.item_meta_chip,

                layoutMeta,

                false

            )

            view.findViewById<ImageView>(R.id.imgIcon)
                .setImageResource(item.icon)

            view.findViewById<TextView>(R.id.txtValue)
                .text = item.value

            layoutMeta.addView(view)

        }

    }

    */
    private fun formatCount(value: Long): String {
        return when {
            value >= 1_000_000_000L -> "%.1fB".format(value / 1_000_000_000f)
            value >= 1_000_000L -> "%.1fM".format(value / 1_000_000f)
            value >= 1_000L -> "%.1fK".format(value / 1_000f)
            else -> value.toString()
        }
    }
}

