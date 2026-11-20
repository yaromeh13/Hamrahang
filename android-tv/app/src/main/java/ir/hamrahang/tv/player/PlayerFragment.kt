package ir.hamrahang.tv.player

import android.os.Bundle
import android.util.Log
import android.view.View
import androidx.fragment.app.Fragment
import androidx.media3.ui.PlayerView
import ir.hamrahang.tv.R
import androidx.core.view.isVisible
import androidx.activity.OnBackPressedCallback
import androidx.lifecycle.lifecycleScope
import android.view.KeyEvent
import android.view.ViewTreeObserver
import android.view.ViewGroup
import android.view.Gravity
import android.net.Uri
import android.widget.ScrollView
import android.widget.FrameLayout
import android.widget.ImageButton
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.RadioGroup
import android.widget.RadioButton
import android.widget.SeekBar
import android.widget.TextView
import androidx.appcompat.widget.SwitchCompat
import androidx.media3.common.Player
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.owner.OwnerRepository
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackQueueAdapter
import ir.hamrahang.tv.ui.hero.HeroArtistsBinder
import ir.hamrahang.tv.ui.hero.HeroMetaBinder
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.launch

import ir.hamrahang.tv.lyrics.LyricsAdapter
import ir.hamrahang.tv.lyrics.LyricsAutoScroller
import ir.hamrahang.tv.lyrics.LyricsParser
import ir.hamrahang.tv.lyrics.LyricsPreferences
import ir.hamrahang.tv.lyrics.LyricsSettings
import ir.hamrahang.tv.lyrics.LyricsEngine
import ir.hamrahang.tv.lyrics.LyricsViewMode
import ir.hamrahang.tv.lyrics.ParsedLyrics



class PlayerFragment : Fragment(R.layout.fragment_player) {
    private lateinit var leftHeaderContainer: FrameLayout
    private lateinit var playerTitleContainer: View
    private lateinit var txtPlayerTitle: TextView
    private lateinit var txtPlayerTitleReflection: ReflectionTextView
    private var lastPlayerIconMode: MediaType? = null
    private var sourcePanelGeneration = 0
    private var isRotatingHeader = false
    private var headerImgCover: ImageView? = null
    private var headerTxtTitle: TextView? = null
    private var headerTxtAlbum: TextView? = null
    private var headerLayoutArtists: LinearLayout? = null
    private var headerLayoutMeta: LinearLayout? = null
    private var coverAnimator: android.animation.ObjectAnimator? = null
    private var rotationStartPending = false
    private var lastHeaderSongId: String? = null
    private lateinit var playerOwnerImage: ImageView
    private var lastPlayerOwnerId: Long? = null
    private var playerOwnerRequestId = 0
    private lateinit var metaBinder: HeroMetaBinder
    private var playerView: PlayerView? = null
    private var hideRunnable: Runnable? = null
    private lateinit var imgCover: ImageView
    private lateinit var overlay: View
    private lateinit var seekBar: SeekBar
    private lateinit var txtCurrentTime: TextView
    private lateinit var txtDuration: TextView
    private lateinit var btnPlayPause: ImageButton
    private lateinit var btnNext: ImageButton
    private lateinit var btnPrev: ImageButton
    private lateinit var btnRewind15: ImageButton
    private lateinit var btnForward15: ImageButton
    private lateinit var btnShuffle: ImageButton
    private lateinit var btnRepeat: ImageButton
    private lateinit var btnPlayerSettings: ImageButton
    private lateinit var settingsPanel: View
    private lateinit var switchHeader: SwitchCompat
    private lateinit var switchrotatingHeader: SwitchCompat
    private lateinit var switchController: SwitchCompat
    private lateinit var headerContainer: View
    private lateinit var controlContainer: View
    private lateinit var seekContainer: View
    private lateinit var lyricsContainer: View
    private lateinit var lyricsRecycler: RecyclerView
    private lateinit var lyricsAdapter: LyricsAdapter
    private lateinit var txtNoLyrics: TextView
    private lateinit var lyricsSettings: LyricsSettings
    private var lyricsPreferences = LyricsPreferences()
    private val lyricsEngine = LyricsEngine()
    private var parsedLyrics = ParsedLyrics(emptyList())
    private var lastLyricsBody: String? = null
    private var lastLyricsActiveIndex = Int.MIN_VALUE
    private lateinit var switchLyrics: SwitchCompat
    private lateinit var switchLyricsVideo: SwitchCompat
    private lateinit var btnLyrics: ImageButton
    private lateinit var btnSource: ImageButton
    private lateinit var panelDynamicScroll: ScrollView
    private lateinit var panelDynamicContent: LinearLayout
    private var sizeSeekBar: SeekBar? = null
    private var panelMode: String = "GENERAL"
    private var panelReturnFocus: View? = null
    private lateinit var btnQueue: ImageButton
    private lateinit var queuePanel: View
    private lateinit var btnCloseQueue: View
    private lateinit var queueRecycler: RecyclerView
    private lateinit var queueAdapter: PlaybackQueueAdapter
    private lateinit var settingsColumnAudio: LinearLayout
    private lateinit var settingsColumnVideo: LinearLayout
    private lateinit var switchVideoImage: SwitchCompat
    private lateinit var switchVideoCover: SwitchCompat
    private lateinit var switchSubtitle: SwitchCompat
    private lateinit var settingsQualityGroup: RadioGroup
    private lateinit var rowItemVideoImage: LinearLayout
    private lateinit var rowItemVideoCover: LinearLayout
    private lateinit var rowItemSubtitle: LinearLayout
    private lateinit var rowItemLyrics: LinearLayout
    private lateinit var playbackBackdrop: ImageView
    private var lastBackdropUrl: Any? = null
    private var controllerVisibleBeforePanel = false
    private var isOverlayPanelOpen = false
    private var focusChangeListener: ViewTreeObserver.OnGlobalFocusChangeListener? = null

    @androidx.media3.common.util.UnstableApi
    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        playerView = view.findViewById(R.id.playerView)
        playerView?.hideController()
        playerView?.player = PlayerManager.getPlayer(requireContext())
        Log.d("PLAYER", "ArtworkView = ${playerView?.artworkDisplayMode}")
        Log.d("PLAYER", playerView?.childCount.toString())

        for (i in 0 until (playerView?.childCount ?: 0)) {
            Log.d(
                "PLAYER_CHILD",
                playerView!!.getChildAt(i).javaClass.name
            )
        }

        playerView?.apply {
            useController = false
            useArtwork = false
            defaultArtwork = null
            artworkDisplayMode = androidx.media3.ui.PlayerView.ARTWORK_DISPLAY_MODE_OFF
            setShutterBackgroundColor(android.graphics.Color.TRANSPARENT)
        }

        Log.d("PLAYER", playerView?.player?.currentMediaItem?.localConfiguration?.uri.toString())

        Log.d(
            "PLAYER",
            PlayerManager.getCurrentMedia()?.type.toString()
        )

        Log.d("PLAYER_FLOW", "fragment opened")
        //metaBinder = HeroMetaBinder( view.findViewById(R.id.metaContainer) )
        leftHeaderContainer = view.findViewById(R.id.leftHeaderContainer)
        switchHeader(rotating = false)
        overlay = view.findViewById(R.id.playerOverlay)
        playerTitleContainer = view.findViewById(R.id.playerTitleContainer)
        txtPlayerTitle = view.findViewById(R.id.txtPlayerTitle)
        txtPlayerTitleReflection = view.findViewById(R.id.txtPlayerTitleReflection)
        playerOwnerImage = view.findViewById(R.id.playerOwnerImage)
        playerOwnerImage.visibility = View.GONE

        seekBar = view.findViewById(R.id.seekBar)
        txtCurrentTime = view.findViewById(R.id.txtCurrentTime)
        txtDuration = view.findViewById(R.id.txtDuration)

        btnPlayPause = view.findViewById(R.id.btnPlayPause)
        btnPlayPause.setOnClickListener {
            PlayerManager.toggle()
            showControls()
        }

        btnNext = view.findViewById(R.id.btnNext)
        btnNext.setOnClickListener {
            PlaybackManager.next()
            showControls()
        }

        btnPrev = view.findViewById(R.id.btnPrev)
        btnPrev.setOnClickListener {
            PlaybackManager.previous()
            showControls()
        }

        btnRewind15 = view.findViewById(R.id.btnRewind15)
        btnRewind15.setOnClickListener {
            val newPosition = (PlayerManager.getCurrentPosition() - 15_000).coerceAtLeast(0L)
            PlayerManager.seekTo(newPosition)
            showControls()
        }

        btnForward15 = view.findViewById(R.id.btnForward15)
        btnForward15.setOnClickListener {
            val duration = PlayerManager.getDuration()
            val newPosition = (PlayerManager.getCurrentPosition() + 15_000).let {
                if (duration > 0) it.coerceAtMost(duration) else it
            }
            PlayerManager.seekTo(newPosition)
            showControls()
        }

        btnShuffle = view.findViewById(R.id.btnShuffle)
        btnShuffle.setOnClickListener {
            PlayerManager.toggleShuffle()
            showControls()
        }

        btnRepeat = view.findViewById(R.id.btnRepeat)
        btnRepeat.setOnClickListener {
            PlayerManager.toggleRepeat()
            showControls()
        }

        settingsPanel = view.findViewById(R.id.playerSettingsPanel)
        focusChangeListener = ViewTreeObserver.OnGlobalFocusChangeListener { oldFocus, newFocus ->

            if (settingsPanel.isVisible &&
                oldFocus != null &&
                isViewInside(settingsPanel, oldFocus) &&
                (newFocus == null || !isViewInside(settingsPanel, newFocus))
            ) {
                settingsPanel.post {
                    if (settingsPanel.isVisible) {
                        val fallback = if (panelDynamicScroll.isVisible) {
                            firstFocusable(panelDynamicContent)
                        } else if (PlayerState.playbackInfo.value.mediaType == MediaType.VIDEO) {
                            rowItemSubtitle
                        } else {
                            rowItemLyrics
                        }
                        fallback?.requestFocus()
                    }
                }
            }

            // اگه فوکوس داخل کنترلر پلیره، تایمر رو ریست کن
            if (newFocus != null &&
                (isViewInside(controlContainer, newFocus) || isViewInside(seekContainer, newFocus))
            ) {
                scheduleHide()
            }

        }
        view.viewTreeObserver.addOnGlobalFocusChangeListener(focusChangeListener)

        switchLyrics = view.findViewById(R.id.switchLyrics)
        switchLyricsVideo = view.findViewById(R.id.switchLyricsVideo)
        switchHeader = view.findViewById(R.id.switchHeader)
        switchrotatingHeader = view.findViewById(R.id.switchHeaderRotate)
        switchController = view.findViewById(R.id.switchController)
        settingsColumnAudio = view.findViewById(R.id.settingsColumnAudio)
        settingsQualityGroup = view.findViewById(R.id.qualityRadioGroup)

        val currentState = PlayerState.uiState.value
        switchLyrics.isChecked = currentState.playerlyricsVisible
        switchLyricsVideo.isChecked = currentState.playerlyricsVisible
        switchHeader.isChecked = currentState.headerVisible
        switchrotatingHeader.isChecked = currentState.rotatingHeaderEnabled
        switchController.isChecked = currentState.controllerPinned

        lyricsContainer = view.findViewById(R.id.lyricsContainer)
        txtNoLyrics = view.findViewById(R.id.txtNoLyrics)
        lyricsSettings = LyricsSettings(requireContext())
        lyricsPreferences = lyricsSettings.read()
        PlayerState.setPlayerLyricsVisible(lyricsPreferences.enabled)
        lyricsAdapter = LyricsAdapter(requireContext())
        lyricsRecycler = RecyclerView(requireContext()).apply {
            layoutManager = LinearLayoutManager(requireContext())
            adapter = lyricsAdapter
            itemAnimator = null
            overScrollMode = View.OVER_SCROLL_NEVER
            isFocusable = false
            isFocusableInTouchMode = false
            setHasFixedSize(false)
        }
        (lyricsContainer as FrameLayout).addView(
            lyricsRecycler,
            FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT
            )
        )
        headerContainer = view.findViewById(R.id.headerContainer)
        controlContainer = view.findViewById(R.id.controlContainer)
        seekContainer = view.findViewById(R.id.seekContainer)


        rowItemLyrics = view.findViewById(R.id.rowItemLyrics)
        rowItemLyrics.setOnClickListener {
            switchLyrics.isChecked = !switchLyrics.isChecked
            lyricsPreferences = lyricsPreferences.copy(enabled = switchLyrics.isChecked)
            lyricsSettings.update(lyricsPreferences)
            PlayerState.setPlayerLyricsVisible(switchLyrics.isChecked)
        }

        view.findViewById<LinearLayout>(R.id.rowItemLyricsVideo).setOnClickListener {
            switchLyricsVideo.isChecked = !switchLyricsVideo.isChecked
            lyricsPreferences = lyricsPreferences.copy(enabled = switchLyricsVideo.isChecked)
            lyricsSettings.update(lyricsPreferences)
            PlayerState.setPlayerLyricsVisible(switchLyricsVideo.isChecked)
        }

        val rowItemHeader = view.findViewById<LinearLayout>(R.id.rowItemHeader)
        rowItemHeader.setOnClickListener {
            switchHeader.isChecked = !switchHeader.isChecked
            val enabled = switchHeader.isChecked
            // Header switch controls the entire audio header presentation,
            // including the independent centered title/reflection overlay.
            playerTitleContainer.visibility =
                if (enabled && PlayerState.playbackInfo.value.mediaType != MediaType.VIDEO)
                    View.VISIBLE else View.GONE
            leftHeaderContainer.visibility =
                if (enabled && PlayerState.playbackInfo.value.mediaType != MediaType.VIDEO)
                    View.VISIBLE else View.GONE
            PlayerState.setHeaderVisible(enabled)
        }
        val rowItemHeaderRotate = view.findViewById<LinearLayout>(R.id.rowItemHeaderRotate)
        rowItemHeaderRotate.setOnClickListener {
            switchrotatingHeader.isChecked = !switchrotatingHeader.isChecked
            val enabled = switchrotatingHeader.isChecked
            PlayerState.setRotatingHeaderEnabled(enabled)
            // Enabling the rotating header must also make the header visible.
            if (enabled && !switchHeader.isChecked) {
                switchHeader.isChecked = true
                PlayerState.setHeaderVisible(true)
            }
        }
        val rowItemController = view.findViewById<LinearLayout>(R.id.rowItemController)
        rowItemController.setOnClickListener {
            switchController.isChecked = !switchController.isChecked
            PlayerState.setControllerPinned(switchController.isChecked)
        }
        playbackBackdrop = view.findViewById(R.id.playback_backdrop)
        settingsColumnAudio = view.findViewById(R.id.settingsColumnAudio)
        settingsColumnVideo = view.findViewById(R.id.settingsColumnVideo)
        switchVideoImage = view.findViewById(R.id.switchVideoImage)
        switchVideoCover = view.findViewById(R.id.switchVideoCover)
        switchSubtitle = view.findViewById(R.id.switchSubtitle)

        switchVideoImage.isChecked = currentState.videoImageEnabled
        switchVideoCover.isChecked = currentState.videoCoverEnabled

        rowItemVideoImage = view.findViewById(R.id.rowItemVideoImage)
        rowItemVideoImage.setOnClickListener {
            switchVideoImage.isChecked = !switchVideoImage.isChecked
            PlayerState.setVideoImageEnabled(switchVideoImage.isChecked)
        }

        rowItemVideoCover = view.findViewById(R.id.rowItemVideoCover)
        rowItemVideoCover.setOnClickListener {
            switchVideoCover.isChecked = !switchVideoCover.isChecked
            PlayerState.setVideoCoverEnabled(switchVideoCover.isChecked)
        }

        rowItemSubtitle = view.findViewById(R.id.rowItemSubtitle)
        btnPlayerSettings = view.findViewById(R.id.btnPlayerSettings)
        btnPlayerSettings.setOnClickListener {
            openSettingsPanel("GENERAL", btnPlayerSettings)
        }
        btnLyrics = view.findViewById(R.id.btnLyrics)
        btnLyrics.setOnClickListener {
            openSettingsPanel("LYRICS", btnLyrics)
        }
        btnSource = view.findViewById(R.id.btnSource)
        btnSource.setOnClickListener {
            openSettingsPanel("SOURCE", btnSource)
        }
        loadPlayerActionIcons(PlayerState.playbackInfo.value)
        btnQueue = view.findViewById(R.id.btnQueue)
        queuePanel = view.findViewById(R.id.playerQueuePanel)
        panelDynamicScroll = view.findViewById(R.id.playerPanelDynamicScroll)
        panelDynamicContent = view.findViewById(R.id.playerPanelDynamicContent)
        btnCloseQueue = view.findViewById(R.id.btnCloseQueue)
        btnQueue.setOnClickListener {
            toggleQueuePanel()
        }

        btnCloseQueue.setOnClickListener {
            closeQueuePanel()
        }

        queueRecycler = view.findViewById(R.id.listQueue)
        queueRecycler.layoutManager = LinearLayoutManager(requireContext())
        queueAdapter = PlaybackQueueAdapter {
            PlaybackManager.play(it)
        }
        queueRecycler.adapter = queueAdapter

        playerView?.isFocusable = false
        view.isFocusable = true
        view.isFocusableInTouchMode = true

        view.post {
            view.requestFocus()
        }
        view.setOnKeyListener { _, _, event ->

            if (event.action != KeyEvent.ACTION_DOWN)
                return@setOnKeyListener false

            val isShown =
                PlayerState.uiState.value.controllerPinned ||
                        PlayerState.uiState.value.controllerVisible
            if (!isShown) {
                showControls()
                overlay.post {
                    btnPlayPause.requestFocus()
                }
                return@setOnKeyListener true
            } else {
                scheduleHide()
            }
            false
        }

        seekBar.setOnSeekBarChangeListener(
            object : SeekBar.OnSeekBarChangeListener {

                override fun onProgressChanged(
                    seekBar: SeekBar?,
                    progress: Int,
                    fromUser: Boolean
                ) {
                }

                override fun onStartTrackingTouch(seekBar: SeekBar?) {
                    showControls()
                }

                override fun onStopTrackingTouch(seekBar: SeekBar?) {

                    PlayerManager.seekTo(
                        seekBar?.progress?.toLong() ?: 0L
                    )

                    scheduleHide()
                }
            }
        )

        // Android TV remote seeking: consume DPAD keys while the seek bar
        // owns focus and seek in predictable 5-second steps.
        seekBar.isFocusable = true
        seekBar.isFocusableInTouchMode = true
        seekBar.setOnFocusChangeListener { _, hasFocus ->
            if (hasFocus) {
                hideRunnable?.let { overlay.removeCallbacks(it) }
                showControls()
            } else {
                scheduleHide()
            }
        }
        seekBar.setOnKeyListener { _, keyCode, event ->
            if (event.action != KeyEvent.ACTION_DOWN) {
                return@setOnKeyListener true
            }

            val stepMs = 5_000L
            val current = seekBar.progress.toLong()
            val duration = PlayerState.uiState.value.duration
                .takeIf { it > 0L } ?: seekBar.max.toLong()

            when (keyCode) {
                KeyEvent.KEYCODE_DPAD_LEFT -> {
                    val target = (current - stepMs).coerceAtLeast(0L)
                    seekBar.progress = target.toInt()
                    PlayerManager.seekTo(target)
                    txtCurrentTime.text = format(target)
                }
                KeyEvent.KEYCODE_DPAD_RIGHT -> {
                    val target = (current + stepMs).coerceAtMost(duration)
                    seekBar.progress = target.toInt()
                    PlayerManager.seekTo(target)
                    txtCurrentTime.text = format(target)
                }
                KeyEvent.KEYCODE_DPAD_CENTER,
                KeyEvent.KEYCODE_ENTER -> {
                    seekBar.clearFocus()
                    btnPlayPause.requestFocus()
                }
                else -> return@setOnKeyListener false
            }
            showControls()
            true
        }

        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.uiState.collect { ui ->

                // Keep the shared lyrics visibility toggle synchronized in audio/video settings.
                switchLyrics.isChecked = ui.playerlyricsVisible
                switchLyricsVideo.isChecked = ui.playerlyricsVisible

                // فقط وضعیت UI
                if (ui.rotatingHeaderEnabled != isRotatingHeader) {
                    switchHeader(ui.rotatingHeaderEnabled)
                    bindHeaderInfo(PlayerState.playbackInfo.value)
                }

                //controlContainer.isVisible = ui.controllerVisible
                //lyricsContainer.isVisible = ui.playerlyricsVisible
                applyOverlayVisibility(ui)
                bindLyrics(PlayerState.playbackInfo.value, ui.position)

                // Progress
                if (ui.duration > 0) {
                    txtCurrentTime.text = format(ui.position)
                    txtDuration.text = format(ui.duration)

                    if (!seekBar.isPressed) {
                        seekBar.max = ui.duration.toInt()
                        seekBar.progress = ui.position.toInt()
                    }
                }

                // Keep the rotating cover strictly synchronized with playback.
                syncHeaderRotation(ui.playbackState)

                // Playback state
                when (ui.playbackState) {
                    PlaybackState.PLAYING -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_pause)
                    }

                    PlaybackState.LOADING -> {
                        btnPlayPause.isEnabled = false
                        btnPlayPause.setImageResource(R.drawable.ic_loading)
                    }

                    PlaybackState.PAUSED,
                    PlaybackState.IDLE -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_play_arrow)
                    }

                    PlaybackState.ERROR -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_error)
                    }
                }

                btnShuffle.alpha =
                    if (ui.shuffle) 1f else 0.5f

                btnRepeat.alpha =
                    if (ui.repeatMode == Player.REPEAT_MODE_OFF)
                        0.5f
                    else
                        1f

                applyOverlayVisibility(ui)
            }
        }

        /*viewLifecycleOwner.lifecycleScope.launch {

            PlayerState.uiState.collect { ui ->

                if (ui.rotatingHeaderEnabled != isRotatingHeader) {
                    switchHeader(ui.rotatingHeaderEnabled)
                }

                bindHeaderUi(ui)
                ui.song?.let {
                    playerView?.useArtwork = false
                    playerView?.defaultArtwork = null
                }
                headerContainer.isVisible = ui.headerVisible
                controlContainer.isVisible = ui.controllerVisible
                lyricsContainer.isVisible = ui.playerlyricsVisible

                if (ui.duration > 0) {
                    txtCurrentTime.text = format(ui.position)
                    txtDuration.text = format(ui.duration)
                    if (!seekBar.isPressed) {
                        seekBar.max = ui.duration.toInt()
                        seekBar.progress = ui.position.toInt()
                    }
                }
                // این بخش اضافه بشه:
                when (ui.playbackState) {
                    PlaybackState.PLAYING -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_pause)
                    }
                    PlaybackState.LOADING -> {
                        btnPlayPause.isEnabled = false
                        btnPlayPause.setImageResource(R.drawable.ic_loading)
                    }
                    PlaybackState.PAUSED, PlaybackState.IDLE -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_play_arrow)
                    }
                    PlaybackState.ERROR -> {
                        btnPlayPause.isEnabled = true
                        btnPlayPause.setImageResource(R.drawable.ic_error)
                    }
                }

                btnShuffle.alpha = if (ui.shuffle) 1f else 0.5f
                btnRepeat.alpha = if (ui.repeatMode == Player.REPEAT_MODE_OFF) 0.5f else 1f

                val isVideo = ui.mediaType == MediaType.VIDEO
                settingsColumnAudio.isVisible = !isVideo
                settingsColumnVideo.isVisible = isVideo

                applyVideoDisplayMode(ui)
                applyOverlayVisibility(ui)
            }
        }*/
        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.playbackInfo.collect { info ->

                bindHeaderInfo(info)
                bindPlayerOwner(info)

                val selectedLyrics = LyricsParser.selectEntry(
                    info.lyricsEntries,
                    lyricsPreferences.language
                )?.body ?: info.lyrics
                val hasLyrics = !selectedLyrics.isNullOrBlank()
                if (hasLyrics && lyricsPreferences.enabled &&
                    !PlayerState.uiState.value.playerlyricsVisible
                ) {
                    PlayerState.setPlayerLyricsVisible(true)
                }
                if (selectedLyrics != lastLyricsBody) {
                    lastLyricsBody = selectedLyrics
                    lyricsEngine.load(selectedLyrics)
                    parsedLyrics = lyricsEngine.current()
                    lastLyricsActiveIndex = Int.MIN_VALUE
                }
                txtNoLyrics.isVisible = !hasLyrics
                lyricsRecycler.isVisible = hasLyrics
                bindLyrics(info, PlayerState.uiState.value.position)
                if (!hasLyrics && PlayerState.uiState.value.playerlyricsVisible) {
                    switchLyrics.isChecked = false
                    switchLyricsVideo.isChecked = false
                    PlayerState.setPlayerLyricsVisible(false)
                }

                val isVideo = info.mediaType == MediaType.VIDEO

                settingsColumnAudio.isVisible = !isVideo
                settingsColumnVideo.isVisible = isVideo

                applyVideoDisplayMode(
                    PlayerState.uiState.value,
                    info
                )
            }
        }
        requireActivity().onBackPressedDispatcher.addCallback(
            viewLifecycleOwner,
            object : OnBackPressedCallback(true) {
                override fun handleOnBackPressed() {
                    if (closePanels()) {
                        return
                    }
                    isEnabled = false
                    (requireActivity() as? MainActivity)
                        ?.closeFullscreenPlayer()
                }
            }
        )
        observeQueue()
    }

    override fun onResume() {
        super.onResume()
        if (::btnPlayPause.isInitialized) btnPlayPause.requestFocus()
        if (isAdded) syncHeaderRotation(PlayerState.uiState.value.playbackState)
    }

    override fun onPause() {
        // Do not let the artwork animator run while the player screen is stopped.
        coverAnimator?.pause()
        super.onPause()
    }

    private fun applyOverlayVisibility(ui: PlayerUiState) {

        val info = PlayerState.playbackInfo.value
        val isVideo = info.mediaType == MediaType.VIDEO

        val showAudioHeader = ui.headerVisible && !isVideo

        // The title/reflection is a separate overlay, so bind it explicitly
        // to the same Header switch as the cover, metadata and artists.
        playerTitleContainer.visibility =
            if (showAudioHeader) View.VISIBLE else View.GONE

        leftHeaderContainer.visibility =
            if (showAudioHeader) View.VISIBLE else View.GONE

        // Lyrics may keep this parent container visible, but must never
        // force the header title or artwork back on.
        headerContainer.visibility =
            if (showAudioHeader || ui.playerlyricsVisible) View.VISIBLE
            else View.GONE

        lyricsContainer.visibility =
            if (ui.playerlyricsVisible) View.VISIBLE
            else View.GONE

        // وقتی Settings یا Queue باز است،
        // کنترلر همیشه باید مخفی بماند؛
        // حتی اگر controllerPinned = true باشد.
        val showController =
            !isOverlayPanelOpen &&
                    (ui.controllerPinned || ui.controllerVisible)

        if (showController) {
            seekContainer.visibility = View.VISIBLE
            controlContainer.visibility = View.VISIBLE
        } else {
            seekContainer.visibility = View.GONE
            controlContainer.visibility = View.GONE
        }

        // The web player shows #player-owner only while its controls are hidden.
        updatePlayerOwnerVisibility()
    }

    private fun bindLyrics(info: PlaybackInfoState, positionMs: Long) {
        val language = if (lyricsPreferences.language == "auto") "fa" else lyricsPreferences.language
        val body = LyricsParser.selectEntry(
            info.lyricsEntries,
            language
        )?.body ?: info.lyrics

        if (body != lastLyricsBody) {
            lastLyricsBody = body
            lyricsEngine.load(body)
            parsedLyrics = lyricsEngine.current()
            lastLyricsActiveIndex = Int.MIN_VALUE
        }

        val video = info.mediaType == MediaType.VIDEO
        val mode = if (video) LyricsViewMode.LINE else lyricsPreferences.viewMode
        val frame = lyricsEngine.frame(
            positionMs = positionMs,
            preferences = lyricsPreferences.copy(viewMode = mode),
            isVideo = video
        )
        lyricsAdapter.submit(
            frame,
            lyricsPreferences.copy(viewMode = mode),
            parsedLyrics.hasTimedLines
        )

        if (frame.activeIndex != lastLyricsActiveIndex) {
            lastLyricsActiveIndex = frame.activeIndex
            val target = when (mode) {
                LyricsViewMode.LINE, LyricsViewMode.CARD -> 0
                else -> frame.activeIndex
            }
            if (target >= 0 && lyricsRecycler.isAttachedToWindow) {
                LyricsAutoScroller.centerOn(lyricsRecycler, target)
            }
        }
    }

    private fun showControls() {
        val alreadyVisible =
            PlayerState.uiState.value.controllerVisible ||
                    PlayerState.uiState.value.controllerPinned
        PlayerState.setControllerVisible(true)
        if (!alreadyVisible) {
            seekContainer.alpha = 0f
            controlContainer.alpha = 0f
            seekContainer.visibility = View.VISIBLE
            controlContainer.visibility = View.VISIBLE
            seekContainer.animate()
                .alpha(1f)
                .setDuration(180)
            controlContainer.animate()
                .alpha(1f)
                .setDuration(180)
        }
        scheduleHide()
    }

    private fun hideControls() {
        PlayerState.setControllerVisible(false)
        seekContainer.animate()
            .alpha(0f)
            .setDuration(220)
            .withEndAction {
                seekContainer.visibility = View.GONE
            }
        controlContainer.animate()
            .alpha(0f)
            .setDuration(220)
            .withEndAction {
                controlContainer.visibility = View.GONE
            }
    }

    private fun scheduleHide() {
        hideRunnable?.let {overlay.removeCallbacks(it)}
        if (PlayerState.uiState.value.controllerPinned || seekBar.hasFocus()) return
        //if (!PlayerManager.isPlaying()) return
        hideRunnable = Runnable {
            if (!seekBar.isPressed && !seekBar.hasFocus() &&
                //PlayerManager.isPlaying() &&
                PlayerState.uiState.value.controllerVisible &&
                !PlayerState.uiState.value.controllerPinned) {
                hideControls()
            }
        }
        overlay.postDelayed(hideRunnable!!, 4000)
    }
    private fun openSettingsPanel(mode: String = "GENERAL", returnFocus: View? = null) {
        closeOtherPanel(settingsPanel)
        if (!settingsPanel.isVisible) {
            isOverlayPanelOpen = true
            controllerVisibleBeforePanel = PlayerState.uiState.value.controllerVisible
            hideRunnable?.let { overlay.removeCallbacks(it) }
            hideRunnable = null
            seekContainer.visibility = View.GONE
            controlContainer.visibility = View.GONE
            PlayerState.setControllerVisible(false)
            settingsPanel.visibility = View.VISIBLE
        }
        if (returnFocus != null) panelReturnFocus = returnFocus
        panelMode = mode
        renderSharedPanel(mode)
    }

    private fun renderSharedPanel(mode: String, focusKey: String? = null) {
        val panel = settingsPanel as ViewGroup
        for (index in 0 until panel.childCount) {
            val child = panel.getChildAt(index)
            child.visibility = if (child == panelDynamicScroll) View.GONE else View.VISIBLE
        }
        if (mode == "GENERAL") {
            val isVideo = PlayerState.playbackInfo.value.mediaType == MediaType.VIDEO
            settingsColumnAudio.isVisible = !isVideo
            settingsColumnVideo.isVisible = isVideo
            setupQualitySettings(PlayerState.playbackInfo.value)
            settingsPanel.post {
                (if (isVideo) rowItemSubtitle else rowItemLyrics).requestFocus()
            }
            return
        }
        for (index in 0 until panel.childCount) {
            val child = panel.getChildAt(index)
            child.visibility = if (child == panelDynamicScroll) View.VISIBLE else View.GONE
        }
        panelDynamicContent.removeAllViews()
        when (mode) {
            "LYRICS" -> buildLyricsPanel()
            "SOURCE" -> buildSourcePanel()
        }
        panelDynamicContent.post {
            val target = focusKey?.let { panelDynamicContent.findViewWithTag<View>(it) }
            (target ?: firstFocusable(panelDynamicContent))?.requestFocus()
        }
    }

    private fun firstFocusable(parent: ViewGroup): View? {
        for (index in 0 until parent.childCount) {
            val child = parent.getChildAt(index)
            if (child.isFocusable && child.visibility == View.VISIBLE) return child
            if (child is ViewGroup) firstFocusable(child)?.let { return it }
        }
        return null
    }

    private fun updateLyricsPreferences(updated: LyricsPreferences) {
        lyricsPreferences = updated
        lyricsSettings.update(updated)
        lastLyricsBody = null
        bindLyrics(PlayerState.playbackInfo.value, PlayerState.uiState.value.position)
    }

    private fun languageLabel(code: String): String = when (code.lowercase()) {
        "auto" -> getString(R.string.lyrics_language_auto)
        "fa", "fa-ir" -> getString(R.string.lyrics_language_persian)
        "en", "en-us", "en-gb" -> getString(R.string.lyrics_language_english)
        "ar" -> getString(R.string.lyrics_language_arabic)
        "tr" -> getString(R.string.lyrics_language_turkish)
        "ku" -> getString(R.string.lyrics_language_kurdish)
        "az" -> getString(R.string.lyrics_language_azerbaijani)
        "de" -> getString(R.string.lyrics_language_german)
        "fr" -> getString(R.string.lyrics_language_french)
        "es" -> getString(R.string.lyrics_language_spanish)
        "ru" -> getString(R.string.lyrics_language_russian)
        else -> code.uppercase()
    }

    private fun addLyricsColumn(
        title: String,
        choices: List<Pair<String, String>>,
        selected: String,
        enabled: Boolean = true,
        onSelected: (String) -> Unit
    ): LinearLayout {
        val column = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.VERTICAL
            layoutParams = LinearLayout.LayoutParams(
                0,
                ViewGroup.LayoutParams.WRAP_CONTENT,
                1f
            )
            setPadding(8, 0, 8, 0)
        }

        column.addView(TextView(requireContext()).apply {
            text = title
            textSize = 18f
            setTextColor(0xFFFFFFFF.toInt())
            setTypeface(typeface, android.graphics.Typeface.BOLD)
            setPadding(0, 0, 0, 12)
        })

        val group = RadioGroup(requireContext()).apply {
            orientation = RadioGroup.VERTICAL
        }

        choices.forEach { (value, label) ->
            val option = RadioButton(requireContext()).apply {
                id = View.generateViewId()
                tag = value
                text = label
                textSize = 14f
                setTextColor(0xFFFFFFFF.toInt())
                setPadding(8, 8, 8, 8)
                background = requireContext().getDrawable(R.drawable.bg_settings_row_focus)
                isFocusable = enabled
                isFocusableInTouchMode = enabled
                isEnabled = enabled
                isChecked = value == selected
                textDirection = View.TEXT_DIRECTION_LOCALE
                setOnClickListener {
                    group.check(id)
                    onSelected(value)
                }
            }
            group.addView(option, RadioGroup.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            ).apply {
                topMargin = 4.dp
            })
        }
        column.addView(group)
        return column
    }

    private fun buildLyricsPanel() {
        val info = PlayerState.playbackInfo.value
        val isVideo = info.mediaType == MediaType.VIDEO

        panelDynamicContent.orientation = LinearLayout.VERTICAL
        panelDynamicContent.removeAllViews()

        val columns = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.HORIZONTAL
            layoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
        }

        val languageChoices = (
            listOf("auto" to languageLabel("auto")) +
                info.lyricsEntries
                    .filter { !it.body.isNullOrBlank() }
                    .mapNotNull { entry ->
                        entry.lng?.let { it to languageLabel(it) }
                    }
                    .distinctBy { it.first.lowercase() }
            )

        columns.addView(addLyricsColumn(
            getString(R.string.lyrics_language_title),
            languageChoices,
            lyricsPreferences.language
        ) { value ->
            updateLyricsPreferences(lyricsPreferences.copy(language = value))
        })

        addLyricsColumn(
            getString(R.string.lyrics_style_title),
            listOf(
                "NORMAL" to getString(R.string.lyrics_style_normal),
                "FOCUS" to getString(R.string.lyrics_style_focus),
                "LINE" to getString(R.string.lyrics_style_line),
                "CARD" to getString(R.string.lyrics_style_card)
            ),
            if (isVideo) LyricsViewMode.LINE.name else lyricsPreferences.viewMode.name,
            enabled = !isVideo
        ) { value ->
            val selected = runCatching { LyricsViewMode.valueOf(value) }
                .getOrDefault(LyricsViewMode.FOCUS)
            updateLyricsPreferences(lyricsPreferences.copy(viewMode = selected))
        }.also { columns.addView(it) }

        columns.addView(addLyricsColumn(
            getString(R.string.lyrics_font_title),
            listOf(
                "" to getString(R.string.lyrics_font_default),
                "f1" to getString(R.string.lyrics_font_vazir),
                "f2" to getString(R.string.lyrics_font_serif),
                "f3" to getString(R.string.lyrics_font_sans),
                "f4" to getString(R.string.lyrics_font_monospace)
            ),
            lyricsPreferences.fontFamily
        ) { value ->
            updateLyricsPreferences(lyricsPreferences.copy(fontFamily = value))
        })

        columns.addView(addLyricsColumn(
            getString(R.string.lyrics_size_title),
            listOf(
                "13" to getString(R.string.lyrics_size_small),
                "16" to getString(R.string.lyrics_size_medium),
                "20" to getString(R.string.lyrics_size_large),
                "24" to getString(R.string.lyrics_size_extra_large)
            ),
            lyricsPreferences.fontSize.toString()
        ) { value ->
            val size = value.toIntOrNull() ?: return@addLyricsColumn
            sizeSeekBar?.progress = size - 12
            updateLyricsPreferences(lyricsPreferences.copy(fontSize = size))
        })

        for (index in 0 until columns.childCount - 1) {
            columns.addView(View(requireContext()).apply {
                layoutParams = LinearLayout.LayoutParams(1.dp, 300.dp).apply {
                    topMargin = 2.dp
                    bottomMargin = 2.dp
                }
                setBackgroundColor(0x33FFFFFF)
            }, index * 2 + 1)
        }
        panelDynamicContent.addView(columns)

        val sizeSection = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(8, 18, 8, 0)
        }
        sizeSection.addView(TextView(requireContext()).apply {
            text = getString(R.string.lyrics_size_title)
            textSize = 16f
            setTextColor(0xFFFFFFFF.toInt())
            setTypeface(typeface, android.graphics.Typeface.BOLD)
        })

        val sizeControls = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
        }
        val sizeLabel = TextView(requireContext()).apply {
            text = getString(R.string.lyrics_size_value, lyricsPreferences.fontSize)
            textSize = 14f
            setTextColor(0xFFFFFFFF.toInt())
            minWidth = 64.dp
        }
        val seekBar = SeekBar(requireContext()).apply {
            max = 20
            progress = lyricsPreferences.fontSize.coerceIn(12, 32) - 12
            isFocusable = true
            isFocusableInTouchMode = true
            layoutParams = LinearLayout.LayoutParams(
                0,
                ViewGroup.LayoutParams.WRAP_CONTENT,
                1f
            )
            setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
                override fun onProgressChanged(bar: SeekBar?, progress: Int, fromUser: Boolean) {
                    val size = progress + 12
                    sizeLabel.text = getString(R.string.lyrics_size_value, size)
                    if (fromUser) {
                        updateLyricsPreferences(lyricsPreferences.copy(fontSize = size))
                    }
                }
                override fun onStartTrackingTouch(bar: SeekBar?) = Unit
                override fun onStopTrackingTouch(bar: SeekBar?) = Unit
            })
        }
        sizeSeekBar = seekBar
        sizeControls.addView(sizeLabel)
        sizeControls.addView(seekBar)
        sizeSection.addView(sizeControls)
        panelDynamicContent.addView(sizeSection)
    }

    private fun loadPlayerActionIcons(info: PlaybackInfoState) {
        val mode = info.mediaType
        if (lastPlayerIconMode == mode) return
        lastPlayerIconMode = mode

        btnLyrics.apply {
            scaleType = ImageView.ScaleType.FIT_CENTER
            setPadding(16.dp, 16.dp, 16.dp, 16.dp)
            setImageResource(R.drawable.ic_lyrics)
        }
        if (mode == MediaType.VIDEO) {
            Glide.with(btnLyrics)
                .load("https://hamrahang.ir/assets/subtitle.png")
                .override(24.dp, 24.dp)
                .placeholder(R.drawable.ic_lyrics)
                .error(R.drawable.ic_lyrics)
                .dontAnimate()
                .into(btnLyrics)
        } else {
            Glide.with(btnLyrics).clear(btnLyrics)
            btnLyrics.setImageResource(R.drawable.ic_lyrics)
        }

        btnSource.apply {
            scaleType = ImageView.ScaleType.FIT_CENTER
            setPadding(16.dp, 16.dp, 16.dp, 16.dp)
            setImageResource(R.drawable.ic_source)
        }
        Glide.with(btnSource).clear(btnSource)
    }

    private fun sourceOwnerThumbUrl(owner: SourceOwner?): String? {
        val meta = owner?.meta ?: return null
        // Exact index.html rule: ico first, then tmb. Keep the key kind because
        // only ico has its final three characters interpreted as the extension.
        val icon = meta.ico?.trim()?.takeIf { it.isNotEmpty() }
        val thumbnail = meta.tmb?.trim()?.takeIf { it.isNotEmpty() }
        val key = icon ?: thumbnail ?: return null
        if (key.startsWith("https://", true) || key.startsWith("http://", true)) return key
        val base = "https://cdn.hamrahang.ir/thumb/"
        if (key.contains(".")) return base + key

        val fileName = if (icon != null && key.length > 3) {
            val ext = key.takeLast(3).lowercase()
            val name = key.dropLast(3)
            if (name.isNotEmpty() && ext.matches(Regex("[a-z0-9]{3}"))) {
                "$name.$ext"
            } else {
                "$key.jpg"
            }
        } else {
            "$key.jpg"
        }
        return base + fileName
    }

    private fun updatePlayerOwnerVisibility() {
        val ui = PlayerState.uiState.value
        val hasImageUrl = (playerOwnerImage.tag as? String)
            ?.let { it.startsWith("http://") || it.startsWith("https://") } == true
        playerOwnerImage.visibility =
            if (hasImageUrl && !ui.controllerVisible && !ui.controllerPinned && !isOverlayPanelOpen)
                View.VISIBLE else View.GONE
    }

    private fun bindPlayerOwner(info: PlaybackInfoState) {
        val selected = info.selectedSource ?: info.sources.firstOrNull()
        val owner = selected?.owner
        val ownerId = owner?.id ?: selected?.user

        if (ownerId == null || ownerId <= 0L) {
            playerOwnerRequestId++
            lastPlayerOwnerId = null
            Glide.with(playerOwnerImage).clear(playerOwnerImage)
            playerOwnerImage.tag = null
            playerOwnerImage.setImageDrawable(null)
            playerOwnerImage.visibility = View.GONE
            return
        }

        val directUrl = sourceOwnerThumbUrl(owner)
        if (!directUrl.isNullOrBlank()) {
            if (lastPlayerOwnerId != ownerId || playerOwnerImage.tag != directUrl) {
                lastPlayerOwnerId = ownerId
                playerOwnerImage.tag = directUrl
                Glide.with(playerOwnerImage)
                    .load(directUrl)
                    .centerCrop()
                    .dontAnimate()
                    .into(playerOwnerImage)
            }
            updatePlayerOwnerVisibility()
            return
        }

        if (lastPlayerOwnerId == ownerId) {
            updatePlayerOwnerVisibility()
            return
        }
        lastPlayerOwnerId = ownerId
        val requestId = ++playerOwnerRequestId
        playerOwnerImage.tag = "owner_$ownerId"
        playerOwnerImage.setImageDrawable(null)
        playerOwnerImage.visibility = View.GONE

        viewLifecycleOwner.lifecycleScope.launch {
            val resolved = runCatching {
                OwnerRepository().resolveByIds(listOf(ownerId))[ownerId]
            }.getOrNull()

            if (!isAdded || requestId != playerOwnerRequestId ||
                PlayerState.playbackInfo.value.selectedSource?.let {
                    (it.owner?.id ?: it.user) != ownerId
                } == true
            ) return@launch

            val url = sourceOwnerThumbUrl(resolved)
            if (url.isNullOrBlank()) return@launch

            playerOwnerImage.tag = url
            Glide.with(playerOwnerImage)
                .load(url)
                .centerCrop()
                .dontAnimate()
                .into(playerOwnerImage)
            updatePlayerOwnerVisibility()
        }
    }

    private fun addPanelLabel(value: String) {
        panelDynamicContent.addView(TextView(requireContext()).apply {
            text = value
            textSize = 15f
            setTextColor(0xFFBDBDC8.toInt())
            setPadding(8, 18, 8, 8)
        })
    }

    private fun buildSourcePanel() {
        val sources = SourceQualityUtils.playableSources(PlayerState.playbackInfo.value.sources)
        if (sources.isEmpty()) {
            addPanelLabel(getString(R.string.source_none))
            return
        }

        renderSourceRows(sources, emptyMap())

        val ownerIds = sources
            .filter { sourceOwnerThumbUrl(it.owner) == null }
            .mapNotNull { it.owner?.id ?: it.user }
            .filter { it > 0L }
            .distinct()

        if (ownerIds.isEmpty()) return

        val requestId = ++sourcePanelGeneration
        viewLifecycleOwner.lifecycleScope.launch {
            val owners = runCatching {
                OwnerRepository().resolveByIds(ownerIds)
            }.getOrDefault(emptyMap())

            if (!isAdded ||
                requestId != sourcePanelGeneration ||
                panelMode != "SOURCE" ||
                !settingsPanel.isVisible
            ) return@launch

            val focusedTag = panelDynamicContent.findFocus()?.tag?.toString()
            renderSourceRows(sources, owners)
            if (focusedTag != null) {
                panelDynamicContent.post {
                    panelDynamicContent.findViewWithTag<View>(focusedTag)?.requestFocus()
                }
            }
        }
    }

    private fun renderSourceRows(
        sources: List<MediaSource>,
        resolvedOwners: Map<Long, SourceOwner>
    ) {
        panelDynamicContent.removeAllViews()
        val selectedUrl = PlayerState.playbackInfo.value.selectedSource?.url
        val isVideo = PlayerState.playbackInfo.value.mediaType == MediaType.VIDEO

        sources.forEachIndexed { index, source ->
            val ownerId = source.owner?.id ?: source.user
            val resolvedOwner = ownerId?.let(resolvedOwners::get)
            val ownerWithThumb = resolvedOwner?.takeIf {
                sourceOwnerThumbUrl(it) != null
            } ?: source.owner?.takeIf {
                sourceOwnerThumbUrl(it) != null
            } ?: resolvedOwner ?: source.owner

            val ownerName = resolvedOwner?.name
                ?.takeIf { it.isNotBlank() && !it.startsWith("User #") }
                ?: source.owner?.name?.takeIf { it.isNotBlank() }
                ?: resolvedOwner?.name?.takeIf { it.isNotBlank() }
                ?: runCatching { Uri.parse(source.url).host }.getOrNull()
                ?: getString(R.string.source_fallback, index + 1)

            val row = LinearLayout(requireContext()).apply {
                orientation = LinearLayout.HORIZONTAL
                gravity = Gravity.CENTER_VERTICAL
                setPadding(12.dp, 5.dp, 12.dp, 5.dp)
                minimumHeight = 64.dp
                isFocusable = true
                isFocusableInTouchMode = true
                isClickable = true
                background = requireContext().getDrawable(R.drawable.bg_player_source_row)
                isSelected = source.url == selectedUrl
                tag = "source_$index"
                layoutParams = LinearLayout.LayoutParams(
                    ViewGroup.LayoutParams.MATCH_PARENT,
                    ViewGroup.LayoutParams.WRAP_CONTENT
                ).apply {
                    topMargin = 4.dp
                    bottomMargin = 4.dp
                }
            }

            row.addView(TextView(requireContext()).apply {
                text = (index + 1).toString()
                textSize = 14f
                gravity = Gravity.CENTER
                setTextColor(0xFF999999.toInt())
                layoutParams = LinearLayout.LayoutParams(38.dp, ViewGroup.LayoutParams.MATCH_PARENT)
            })

            val ownerImage = ImageView(requireContext()).apply {
                layoutParams = LinearLayout.LayoutParams(54.dp, 54.dp).apply {
                    marginEnd = 10.dp
                }
                scaleType = ImageView.ScaleType.CENTER_CROP
                background = android.graphics.drawable.GradientDrawable().apply {
                    setColor(0xFF292929.toInt())
                    cornerRadius = 8.dp.toFloat()
                }
                clipToOutline = true
                setImageResource(R.drawable.ic_general)
            }
            val thumbUrl = sourceOwnerThumbUrl(ownerWithThumb)
            if (!thumbUrl.isNullOrBlank()) {
                Glide.with(ownerImage)
                    .load(thumbUrl)
                    .transform(CenterCrop(), RoundedCorners(8.dp))
                    .placeholder(R.drawable.ic_general)
                    .error(R.drawable.ic_general)
                    .fallback(R.drawable.ic_general)
                    .into(ownerImage)
            }
            row.addView(ownerImage)

            row.addView(TextView(requireContext()).apply {
                text = ownerName
                textSize = 16f
                maxLines = 1
                ellipsize = android.text.TextUtils.TruncateAt.END
                setTextColor(if (source.url == selectedUrl) 0xFFFFFFFF.toInt() else 0xFFE4E4E4.toInt())
                layoutParams = LinearLayout.LayoutParams(
                    0,
                    ViewGroup.LayoutParams.WRAP_CONTENT,
                    1f
                )
            })

            val bit = SourceQualityUtils.parseBit(source.bit)
            row.addView(TextView(requireContext()).apply {
                text = if (bit == null) "—" else if (isVideo) "${bit}p" else "${bit}k"
                textSize = 14f
                setTextColor(0xFFEAEAEA.toInt())
                gravity = Gravity.CENTER
                setPadding(12.dp, 6.dp, 12.dp, 6.dp)
                background = requireContext().getDrawable(R.drawable.bg_source_quality_chip)
            })

            row.setOnClickListener {
                PlayerManager.switchToSource(source)
                updateSourceSelection(sources, source.url)
            }
            panelDynamicContent.addView(row)
        }
    }

    private fun updateSourceSelection(sources: List<MediaSource>, selectedUrl: String) {
        for (index in 0 until panelDynamicContent.childCount) {
            val row = panelDynamicContent.getChildAt(index) as? LinearLayout ?: continue
            val sourceIndex = row.tag?.toString()?.removePrefix("source_")?.toIntOrNull() ?: continue
            val selected = sources.getOrNull(sourceIndex)?.url == selectedUrl
            row.isSelected = selected
            (row.getChildAt(2) as? TextView)?.setTextColor(
                if (selected) 0xFFFF7777.toInt() else 0xFFFFFFFF.toInt()
            )
        }
    }

    private val Int.dp: Int
        get() = (this * resources.displayMetrics.density).toInt()

    private fun closeSettingsPanel() {
        if (!settingsPanel.isVisible) return
        settingsPanel.visibility = View.GONE
        isOverlayPanelOpen = false
        PlayerState.setControllerVisible(controllerVisibleBeforePanel)
        applyOverlayVisibility(PlayerState.uiState.value)
        (panelReturnFocus ?: when (panelMode) {
            "LYRICS" -> btnLyrics
            "SOURCE" -> btnSource
            else -> btnPlayerSettings
        }).requestFocus()
        panelReturnFocus = null
        if (controllerVisibleBeforePanel && !PlayerState.uiState.value.controllerPinned) scheduleHide()
    }

    private fun format(ms: Long): String {
        val total = ms / 1000
        val m = total / 60
        val s = total % 60
        return String.format("%02d:%02d", m, s)
    }

    private fun isViewInside(parent: View, child: View): Boolean {

        var current: View? = child

        while (current != null) {
            if (current == parent) return true
            current = current.parent as? View
        }

        return false
    }
    private fun closePanel(
        panel: View,
        focusView: View
    ): Boolean {

        if (!panel.isVisible) return false

        panel.visibility = View.GONE
        focusView.requestFocus()

        return true
    }

    private fun closePanels(): Boolean {

        if (queuePanel.isVisible) {
            closeQueuePanel()
            return true
        }

        if (settingsPanel.isVisible) {
            closeSettingsPanel()
            return true
        }
        return false
    }

    private fun observeQueue() {

        viewLifecycleOwner.lifecycleScope.launch {

            PlaybackManager.session.collectLatest { session ->

                session ?: return@collectLatest

                queueAdapter.submit(
                    session.items,
                    session.currentIndex
                )
            }
        }
    }

    private fun toggleQueuePanel() {

        if (queuePanel.isVisible) {
            closeQueuePanel()
        } else {
            openQueuePanel()
        }
    }

    private fun openQueuePanel() {
        // اول پنل دیگر را ببند
        closeOtherPanel(queuePanel)
        val offset =
            if (resources.configuration.layoutDirection == View.LAYOUT_DIRECTION_RTL)
                -queuePanel.width.toFloat()
            else
                queuePanel.width.toFloat()

        // پنل باز شد
        isOverlayPanelOpen = true
        // وضعیت کنترلر قبل از Queue را حفظ کن
        controllerVisibleBeforePanel = PlayerState.uiState.value.controllerVisible
        // تایمر را متوقف کن
        hideRunnable?.let { overlay.removeCallbacks(it) }
        hideRunnable = null
        // کنترلر را مخفی کن
        PlayerState.setControllerVisible(false)
        seekContainer.visibility = View.GONE
        controlContainer.visibility = View.GONE
        queuePanel.translationX = offset
        queuePanel.alpha = 0f
        queuePanel.visibility = View.VISIBLE
        queuePanel.animate()
            .translationX(0f)
            .alpha(1f)
            .setDuration(220)
            .start()
        queuePanel.post { btnCloseQueue.requestFocus() }
    }

private fun closeQueuePanel() {

    val offset =
        if (resources.configuration.layoutDirection == View.LAYOUT_DIRECTION_RTL)
            -queuePanel.width.toFloat()
        else
            queuePanel.width.toFloat()

    queuePanel.animate()
        .translationX(offset)
        .alpha(0f)
        .setDuration(220)
        .withEndAction {
            queuePanel.visibility = View.GONE
            // پنل بسته شد
            isOverlayPanelOpen = false
            // وضعیت قبلی کنترلر را برگردان
            PlayerState.setControllerVisible( controllerVisibleBeforePanel )
            // کنترلر بر اساس pinned / visible قبلی نمایش داده شود
            applyOverlayVisibility( PlayerState.uiState.value )
            // فوکوس روی Queue
            btnQueue.requestFocus()
            // اگر کنترلر قبلاً موقتاً باز بوده،
            // تایمرش را دوباره شروع کن.
            if (controllerVisibleBeforePanel &&
                !PlayerState.uiState.value.controllerPinned
            ) { scheduleHide() }
        }
        .start()
}

    private fun closeOtherPanel(except: View) {

        if (except != settingsPanel && settingsPanel.isVisible) {
            settingsPanel.visibility = View.GONE
        }

        if (except != queuePanel && queuePanel.isVisible) {
            queuePanel.animate().cancel()
            queuePanel.visibility = View.GONE
            queuePanel.alpha = 1f
            queuePanel.translationX = 0f
        }

        if (
            !settingsPanel.isVisible &&
            !queuePanel.isVisible
        ) {
            isOverlayPanelOpen = false
        }
    }

    private fun setBackdrop(source: Any?, blur: Boolean = true, full: Boolean = true) {

        if (source == lastBackdropUrl) return
        lastBackdropUrl = source
        playbackBackdrop.scaleType =
            if (full) ImageView.ScaleType.CENTER_CROP
            else ImageView.ScaleType.FIT_CENTER
        val isEmpty = source == null || (source is String && source.isBlank())
        if (isEmpty) {
            playbackBackdrop.setImageResource(R.drawable.bg_default)
            return
        }

        val request = Glide.with(playbackBackdrop)
            .load(source)
            .placeholder(R.drawable.bg_default)
            .error(R.drawable.bg_default)
            .fallback(R.drawable.bg_default)
            .centerCrop()

        if (blur) {
            request.transform(jp.wasabeef.glide.transformations.BlurTransformation(70, 2))
        }

        request
            .transition(com.bumptech.glide.load.resource.drawable.DrawableTransitionOptions.withCrossFade(400))
            .into(playbackBackdrop)
    }
/*
    private fun initPlayer(view: View, url: String) {

        playerView = view.findViewById(R.id.playerView)

        val player = PlayerManager.getPlayer(requireContext())

        playerView?.player = player

        val mediaItem = MediaItem.fromUri(url)

        player.setMediaItem(mediaItem)
        player.prepare()
        player.play()

    }*/
/*
    override fun onStop() {
        super.onStop()
        player?.release()
        player = null
    }
*/

    private fun setupQualitySettings(
        info: PlaybackInfoState
    ) {
        settingsQualityGroup.removeAllViews()
        val sources = SourceQualityUtils.playableSources(info.sources)
        if (sources.isEmpty()) {return}

        val qualities = sources
            .mapNotNull { SourceQualityUtils.parseBit(it.bit) }
            .distinct()
            .sortedDescending()

        val selectedBit = SourceQualityUtils.parseBit(info.selectedSource?.bit)

        qualities.forEach { bit ->

            val radio = createQualityRadio(
                text = bit.toString()
            )

            settingsQualityGroup.addView(radio)
            radio.isChecked = selectedBit == bit

            /*val source = sources.firstOrNull {
                SourceQualityUtils.parseBit(it.bit) == bit
            }*/

            //radio.isChecked = source?.url == info.selectedSource?.url

            radio.setOnClickListener {

                val source =
                    sources.firstOrNull {
                        SourceQualityUtils.parseBit(it.bit) == bit
                    }

                source?.let {
                    selectSource(it)
                }
            }
        }
    }

    private fun createQualityRadio(
        text: String
    ): RadioButton {

        return RadioButton(requireContext()).apply {

            this.text = text

            setTextColor(
                android.graphics.Color.WHITE
            )

            textSize = 16f

            isFocusable = true
            isFocusableInTouchMode = true

            layoutParams =
                LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply {

                    topMargin = 8
                }
        }
    }

    private fun selectSource(
        source: MediaSource
    ) {
        val info = PlayerState.playbackInfo.value

        if (info.selectedSource?.url == source.url) {
            return
        }

        PlayerState.updatePlaybackInfo {
            copy(
                selectedSource = source
            )
        }

        PlayerManager.switchToSource(source)
    }


    private fun switchHeader(rotating: Boolean) {

        if (isRotatingHeader == rotating && leftHeaderContainer.childCount > 0) return

        // A mode switch replaces the cover view, so discard only the old
        // animator here. Playback pause/resume must preserve its current angle.
        stopHeaderRotation(resetCover = true)
        isRotatingHeader = rotating
        leftHeaderContainer.removeAllViews()

        // Keep identical content positions in both layouts; only the cover's
        // shape differs. The rotating layout reuses the proven CircleImage style.
        val layoutRes = if (rotating) {
            R.layout.layout_header_rotating
        } else {
            R.layout.layout_header_simple
        }
        val headerView = layoutInflater.inflate(
            layoutRes,
            leftHeaderContainer,
            false
        )
        leftHeaderContainer.addView(headerView)

        headerImgCover = headerView.findViewById(R.id.imgCover)
        headerImgCover?.apply {
            scaleType = ImageView.ScaleType.CENTER_CROP
            visibility = View.VISIBLE
            alpha = 1f
            rotation = 0f
            bringToFront()
            invalidate()
        }
        headerTxtTitle = headerView.findViewById(R.id.txtTitle)
        headerTxtTitle?.visibility = View.GONE
        headerTxtAlbum = headerView.findViewById(R.id.txtAlbum)
        headerLayoutArtists = headerView.findViewById(R.id.layoutArtists)
        headerLayoutMeta = headerView.findViewById(R.id.layoutMeta)

        metaBinder = HeroMetaBinder(headerLayoutMeta ?: return)

        lastHeaderSongId = null
        //bindHeaderUi(PlayerState.uiState.value)
    }
    private fun bindHeaderInfo(info: PlaybackInfoState) {

        headerTxtTitle?.visibility = View.GONE
        txtPlayerTitle.text = info.title
        txtPlayerTitleReflection.text = info.title
        loadPlayerActionIcons(info)
        metaBinder.bind(info.metadata)
        HeroArtistsBinder.bind(
            headerLayoutArtists ?: return,
            info.artists
        )

        headerImgCover?.let { iv ->
            val fallback = if (info.mediaType == MediaType.VIDEO) {
                R.drawable.ic_video
            } else {
                R.drawable.ic_song
            }
            iv.visibility = View.VISIBLE
            iv.alpha = 1f
            iv.scaleType = ImageView.ScaleType.CENTER_CROP
            Glide.with(iv)
                .load(info.artwork)
                .centerCrop()
                .placeholder(R.drawable.card_bg)
                .error(R.drawable.card_bg)
                .fallback(R.drawable.card_bg)
                .dontAnimate()
                .into(iv)
        }

        syncHeaderRotation(PlayerState.uiState.value.playbackState)
    }

    private fun syncHeaderRotation(state: PlaybackState) {
        if (!isRotatingHeader) {
            coverAnimator?.pause()
            headerImgCover?.rotation = 0f
            return
        }

        if (state == PlaybackState.PLAYING) {
            startHeaderRotation()
        } else {
            // Pause at the exact current angle; do not cancel or recreate it.
            coverAnimator?.pause()
        }
    }

    private fun startHeaderRotation() {
        val cover = headerImgCover ?: return
        if (rotationStartPending) return
        rotationStartPending = true

        // Wait until the replacement header has been measured and attached.
        cover.post {
            rotationStartPending = false
            if (!isAdded || headerImgCover !== cover || !isRotatingHeader ||
                PlayerState.uiState.value.playbackState != PlaybackState.PLAYING
            ) return@post

            cover.pivotX = cover.width / 2f
            cover.pivotY = cover.height / 2f
            val animator = coverAnimator ?: android.animation.ObjectAnimator.ofFloat(
                cover, View.ROTATION, cover.rotation, cover.rotation + 360f
            ).apply {
                duration = 12000
                repeatCount = android.animation.ValueAnimator.INFINITE
                interpolator = android.view.animation.LinearInterpolator()
                coverAnimator = this
            }

            when {
                animator.isPaused -> animator.resume()
                !animator.isStarted -> animator.start()
            }
        }
    }

private fun applyVideoDisplayMode(
    ui: PlayerUiState,
    info: PlaybackInfoState
) {

        setBackdrop( info.artwork, blur = true, full = true )
        val isVideo = info.mediaType == MediaType.VIDEO

        if (!isVideo) {
            playerView?.visibility = View.GONE
            playbackBackdrop.visibility = View.VISIBLE
            return
        }

        when {
            ui.videoCoverEnabled -> {
                playerView?.visibility = View.GONE
                playbackBackdrop.visibility = View.VISIBLE
                setBackdrop( info.artwork, blur = false, full = false )
            }

            ui.videoImageEnabled -> {
                playerView?.visibility = View.VISIBLE
                playbackBackdrop.visibility = View.GONE
                setBackdrop( info.artwork, blur = false, full = true )
            }

            else -> {
                playerView?.visibility = View.GONE
                playbackBackdrop.visibility = View.VISIBLE
                setBackdrop( info.artwork, blur = true, full = true )
            }
        }
    }
    private fun stopHeaderRotation(resetCover: Boolean = false) {
        rotationStartPending = false
        coverAnimator?.cancel()
        coverAnimator = null
        if (resetCover) headerImgCover?.rotation = 0f
    }

    override fun onDestroyView() {
        stopHeaderRotation(resetCover = true)
        if (::playerOwnerImage.isInitialized) {
            Glide.with(playerOwnerImage).clear(playerOwnerImage)
        }
        playerOwnerRequestId++
        super.onDestroyView()
        playerView?.player = null
        playerView = null
        hideRunnable?.let { overlay.removeCallbacks(it) }
        view?.viewTreeObserver?.removeOnGlobalFocusChangeListener(focusChangeListener)
    }
}