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
    private var isRotatingHeader = false
    private var headerImgCover: ImageView? = null
    private var headerTxtTitle: TextView? = null
    private var headerTxtAlbum: TextView? = null
    private var headerLayoutArtists: LinearLayout? = null
    private var headerLayoutMeta: LinearLayout? = null
    private var coverAnimator: android.animation.ObjectAnimator? = null
    private var lastHeaderSongId: String? = null
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
    private var settingsHideRunnable: Runnable? = null
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

            // اگه فوکوس داخل ستینگ، تایمر رو ریست کن
            if (newFocus != null && isViewInside(settingsPanel, newFocus)) {
                scheduleSettingsHide()
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
            PlayerState.setHeaderVisible(switchHeader.isChecked)
        }
        val rowItemHeaderRotate = view.findViewById<LinearLayout>(R.id.rowItemHeaderRotate)
        rowItemHeaderRotate.setOnClickListener {
            switchrotatingHeader.isChecked = !switchrotatingHeader.isChecked
            PlayerState.setRotatingHeaderEnabled(switchrotatingHeader.isChecked)
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
        if (::btnPlayPause.isInitialized) { btnPlayPause.requestFocus() }
    }

    private fun applyOverlayVisibility(ui: PlayerUiState) {

        val info = PlayerState.playbackInfo.value
        val isVideo = info.mediaType == MediaType.VIDEO

        headerContainer.visibility =
            if ((!isVideo && ui.headerVisible) || ui.playerlyricsVisible) View.VISIBLE
            else View.GONE

        leftHeaderContainer.visibility =
            if (ui.headerVisible && !isVideo) View.VISIBLE
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
        if (PlayerState.uiState.value.controllerPinned) return
        //if (!PlayerManager.isPlaying()) return
        hideRunnable = Runnable {
            if (!seekBar.isPressed &&
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

    private fun addPanelLabel(value: String) {
        panelDynamicContent.addView(TextView(requireContext()).apply {
            text = value
            textSize = 15f
            setTextColor(0xFFBDBDC8.toInt())
            setPadding(4, 18, 4, 8)
        })
    }

    private fun addPanelChoice(
        title: String,
        choices: List<Pair<String, String>>,
        selected: String,
        onSelected: (String) -> Unit
    ) {
        addPanelLabel(title)
        val group = RadioGroup(requireContext()).apply {
            orientation = RadioGroup.HORIZONTAL
        }
        choices.forEach { (value, label) ->
            group.addView(RadioButton(requireContext()).apply {
                id = View.generateViewId()
                tag = value
                text = label
                textSize = 14f
                setTextColor(0xFFFFFFFF.toInt())
                setPadding(8, 8, 8, 8)
                isFocusable = true
                isFocusableInTouchMode = true
                isChecked = value == selected
                setOnClickListener {
                    onSelected(value)
                    for (index in 0 until group.childCount) {
                        val child = group.getChildAt(index) as RadioButton
                        child.isChecked = child.tag == value
                    }
                }
            })
        }
        panelDynamicContent.addView(group)
    }

    private fun updateLyricsPreferences(updated: LyricsPreferences) {
        lyricsPreferences = updated
        lyricsSettings.update(updated)
        PlayerState.setPlayerLyricsVisible(updated.enabled)
        lastLyricsBody = null
        bindLyrics(PlayerState.playbackInfo.value, PlayerState.uiState.value.position)
    }

    private fun buildLyricsPanel() {
        val info = PlayerState.playbackInfo.value
        val isVideo = info.mediaType == MediaType.VIDEO
        val enabledRow = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            setPadding(4, 12, 4, 12)
            isFocusable = true
            isFocusableInTouchMode = true
            tag = "lyrics_enabled"
        }
        enabledRow.addView(TextView(requireContext()).apply {
            text = "Show lyrics"
            textSize = 16f
            setTextColor(0xFFFFFFFF.toInt())
            layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
        })
        val enabledSwitch = SwitchCompat(requireContext()).apply {
            isChecked = lyricsPreferences.enabled
            isFocusable = false
            isClickable = false
        }
        enabledRow.addView(enabledSwitch)
        enabledRow.setOnClickListener {
            val next = !lyricsPreferences.enabled
            enabledSwitch.isChecked = next
            updateLyricsPreferences(lyricsPreferences.copy(enabled = next))
        }
        panelDynamicContent.addView(enabledRow)

        val languages = (listOf("auto" to "Automatic") +
            info.lyricsEntries.filter { !it.body.isNullOrBlank() }
                .mapNotNull { entry -> entry.lng?.let { it to it } }
                .distinct())
        addPanelChoice("Lyrics language", languages, lyricsPreferences.language) { value ->
            updateLyricsPreferences(lyricsPreferences.copy(language = value))
        }

        addPanelLabel("Lyrics size")
        val sizeLabel = TextView(requireContext()).apply {
            text = "${lyricsPreferences.fontSize}sp"
            textSize = 14f
            setTextColor(0xFFFFFFFF.toInt())
        }
        panelDynamicContent.addView(sizeLabel)
        val sizeSeek = SeekBar(requireContext()).apply {
            max = 20
            progress = lyricsPreferences.fontSize.coerceIn(12, 32) - 12
            isFocusable = true
        }
        sizeSeek.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(bar: SeekBar?, progress: Int, fromUser: Boolean) {
                val size = progress + 12
                sizeLabel.text = "${size}sp"
                if (fromUser) updateLyricsPreferences(lyricsPreferences.copy(fontSize = size))
            }
            override fun onStartTrackingTouch(bar: SeekBar?) = Unit
            override fun onStopTrackingTouch(bar: SeekBar?) = Unit
        })
        panelDynamicContent.addView(sizeSeek)
        addPanelChoice(
            "Size presets",
            listOf("13" to "SM", "16" to "MD", "20" to "LG", "24" to "XL"),
            lyricsPreferences.fontSize.toString()
        ) { value ->
            val size = value.toIntOrNull() ?: return@addPanelChoice
            sizeSeek.progress = size - 12
            updateLyricsPreferences(lyricsPreferences.copy(fontSize = size))
        }
        addPanelChoice(
            "Lyrics font",
            listOf("" to "Default", "f1" to "F1", "f2" to "F2", "f3" to "F3", "f4" to "F4"),
            lyricsPreferences.fontFamily
        ) { value ->
            updateLyricsPreferences(lyricsPreferences.copy(fontFamily = value))
        }
        if (!isVideo) {
            addPanelChoice(
                "Lyrics style",
                listOf("NORMAL" to "Normal", "LINE" to "Line", "FOCUS" to "Focus", "CARD" to "Card"),
                lyricsPreferences.viewMode.name
            ) { value ->
                val selected = runCatching { LyricsViewMode.valueOf(value) }
                    .getOrDefault(LyricsViewMode.FOCUS)
                updateLyricsPreferences(lyricsPreferences.copy(viewMode = selected))
            }
        }
    }

    private fun buildSourcePanel() {
        val sources = SourceQualityUtils.playableSources(PlayerState.playbackInfo.value.sources)
        if (sources.isEmpty()) {
            addPanelLabel("No sources available")
            return
        }
        val selectedUrl = PlayerState.playbackInfo.value.selectedSource?.url
        sources.forEachIndexed { index, source ->
            val row = LinearLayout(requireContext()).apply {
                orientation = LinearLayout.HORIZONTAL
                gravity = Gravity.CENTER_VERTICAL
                setPadding(12, 12, 12, 12)
                isFocusable = true
                isFocusableInTouchMode = true
                isClickable = true
                background = requireContext().getDrawable(R.drawable.bg_settings_row_focus)
                isSelected = source.url == selectedUrl
                tag = "source_$index"
            }
            val number = TextView(requireContext()).apply {
                text = (index + 1).toString()
                textSize = 14f
                setTextColor(0xFF999999.toInt())
                setPadding(0, 0, 12, 0)
            }
            row.addView(number)
            val ownerImage = ImageView(requireContext()).apply {
                val imageUrl = source.owner?.meta?.tmb
                    ?: source.owner?.meta?.ico
                    ?: source.owner?.meta?.photo
                layoutParams = LinearLayout.LayoutParams(44.dp, 44.dp).apply {
                    marginEnd = 12.dp
                }
                scaleType = ImageView.ScaleType.CENTER_CROP
                if (!imageUrl.isNullOrBlank()) {
                    Glide.with(this)
                        .load(imageUrl)
                        .placeholder(R.drawable.ic_general)
                        .error(R.drawable.ic_general)
                        .into(this)
                } else {
                    setImageResource(R.drawable.ic_general)
                }
            }
            row.addView(ownerImage)
            val label = source.owner?.name?.takeIf { it.isNotBlank() }
                ?: runCatching { Uri.parse(source.url).host }.getOrNull()
                ?: "Source ${index + 1}"
            row.addView(TextView(requireContext()).apply {
                text = label
                textSize = 16f
                setTextColor(if (source.url == selectedUrl) 0xFFFF7777.toInt() else 0xFFFFFFFF.toInt())
                layoutParams = LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f)
            })
            row.addView(TextView(requireContext()).apply {
                val bit = SourceQualityUtils.parseBit(source.bit)
                text = if (bit == null) "—" else if (PlayerState.playbackInfo.value.mediaType == MediaType.VIDEO) "${bit}p" else "${bit}k"
                textSize = 14f
                setTextColor(0xFFCCCCCC.toInt())
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
        cancelSettingsHide()
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

    private fun scheduleSettingsHide() {

        settingsHideRunnable?.let {
            overlay.removeCallbacks(it)
        }

        settingsHideRunnable = Runnable {
            if (settingsPanel.isVisible) {
                closeSettingsPanel()
            }
        }

        overlay.postDelayed(
            settingsHideRunnable!!,
            5000
        )
    }
    private fun cancelSettingsHide() {
        settingsHideRunnable?.let {overlay.removeCallbacks(it)}
        settingsHideRunnable = null
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

        if (panel == settingsPanel) {
            cancelSettingsHide()
        }

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
            cancelSettingsHide()
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

        stopHeaderRotation()
        isRotatingHeader = rotating
        leftHeaderContainer.removeAllViews()

        val layoutRes =
            if (rotating) R.layout.layout_header_rotating
            else R.layout.layout_header_simple

        val headerView = layoutInflater.inflate(layoutRes, leftHeaderContainer, false)
        leftHeaderContainer.addView(headerView)

        headerImgCover = headerView.findViewById(R.id.imgCover)
        headerTxtTitle = headerView.findViewById(R.id.txtTitle)
        headerTxtAlbum = headerView.findViewById(R.id.txtAlbum)
        headerLayoutArtists = headerView.findViewById(R.id.layoutArtists)
        headerLayoutMeta = headerView.findViewById(R.id.layoutMeta)

        metaBinder = HeroMetaBinder(headerLayoutMeta ?: return)

        lastHeaderSongId = null
        //bindHeaderUi(PlayerState.uiState.value)
    }
    private fun bindHeaderInfo(info: PlaybackInfoState) {

        headerTxtTitle?.text = info.title
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
            Glide.with(iv)
                .load(info.artwork)
                .transform(CenterCrop(), RoundedCorners(24))
                .placeholder(fallback)
                .error(fallback)
                .fallback(fallback)
                .into(iv)
        }

        if (isRotatingHeader) {
            if (
                PlayerState.uiState.value.playbackState ==
                PlaybackState.PLAYING
            ) {
                startHeaderRotation()
            } else {
                stopHeaderRotation()
            }
        }
    }

    private fun startHeaderRotation() {
        val cover = headerImgCover ?: return
        if (coverAnimator == null) {
            coverAnimator = android.animation.ObjectAnimator.ofFloat(
                cover, View.ROTATION, 0f, 360f
            ).apply {
                duration = 12000
                repeatCount = android.animation.ValueAnimator.INFINITE
                interpolator = android.view.animation.LinearInterpolator()
                start()
                pause()
            }
        }
        coverAnimator?.resume()
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
    private fun stopHeaderRotation() {
        coverAnimator?.pause()
        coverAnimator?.cancel()
        coverAnimator = null
    }

    override fun onDestroyView() {
        super.onDestroyView()
        playerView?.player = null
        playerView = null
        hideRunnable?.let { overlay.removeCallbacks(it) }
        settingsHideRunnable?.let { overlay.removeCallbacks(it) }
        view?.viewTreeObserver?.removeOnGlobalFocusChangeListener(focusChangeListener)
    }
}