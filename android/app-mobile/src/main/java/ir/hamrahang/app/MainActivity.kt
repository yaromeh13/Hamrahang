package ir.hamrahang.app

import android.animation.ObjectAnimator
import android.animation.ValueAnimator
import android.content.ComponentName
import android.content.Intent
import android.content.res.Configuration
import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.view.animation.LinearInterpolator
import android.widget.ImageButton
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.app.AppCompatDelegate
import androidx.core.content.ContextCompat
import androidx.core.os.LocaleListCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.media3.common.Player
import androidx.media3.session.MediaController
import androidx.media3.session.SessionToken
import com.bumptech.glide.Glide
import com.google.common.util.concurrent.ListenableFuture
import ir.hamrahang.app.databinding.ActivityMainBinding
import ir.hamrahang.app.playback.MobilePlaybackService
import ir.hamrahang.app.ui.PlaceholderFragments
import ir.hamrahang.app.ui.SettingsFragment
import ir.hamrahang.app.ui.device.DeviceLibraryFragment
import ir.hamrahang.app.ui.device.DeviceQueue
import ir.hamrahang.app.ui.home.HomeFragment
import ir.hamrahang.app.ui.home.SeeAllFragment
import ir.hamrahang.app.ui.live.LiveFragment
import ir.hamrahang.app.ui.player.NowPlayingFragment
import ir.hamrahang.app.ui.player.VideoActivity
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlaybackState
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.launch
import java.util.concurrent.Executor

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var controllerFuture: ListenableFuture<MediaController>? = null
    private var mediaController: MediaController? = null
    private val directExecutor = Executor { command -> command.run() }
    private var currentTab = R.id.nav_home
    private var playerMenu = false
    private var discAnimator: ObjectAnimator? = null
    private var miniPlayerWantsVisible = false

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        AppCompatDelegate.setApplicationLocales(
            LocaleListCompat.forLanguageTags(AppState.language)
        )

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.content) { view, insets ->
            val bars = insets.getInsets(
                WindowInsetsCompat.Type.systemBars() or WindowInsetsCompat.Type.displayCutout()
            )
            view.setPadding(bars.left, bars.top, bars.right, 0)
            insets
        }

        currentTab = savedInstanceState?.getInt(KEY_TAB, R.id.nav_home) ?: R.id.nav_home
        playerMenu = savedInstanceState?.getBoolean(KEY_PLAYER_MENU) == true && isLandscape()

        binding.bottomNav.setOnItemSelectedListener { item ->
            currentTab = item.itemId
            showTab(item.itemId)
            true
        }
        binding.bottomNav.setOnItemReselectedListener { popToTabRoot() }

        bindLandscapeBar()
        binding.landDisc.setOnClickListener { openPlayerScreen() }

        if (savedInstanceState == null) {
            showTab(R.id.nav_home)
        } else if (supportFragmentManager.findFragmentById(R.id.fragmentContainer) == null) {
            showTab(currentTab)
        }

        applyOrientationUi(animateMini = false)
        bindMiniPlayer()
    }

    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        outState.putInt(KEY_TAB, currentTab)
        outState.putBoolean(KEY_PLAYER_MENU, playerMenu)
    }

    override fun onStart() {
        super.onStart()
        val token = SessionToken(this, ComponentName(this, MobilePlaybackService::class.java))
        val future = MediaController.Builder(this, token).buildAsync()
        controllerFuture = future
        future.addListener({
            if (controllerFuture === future) {
                runCatching { future.get() }.onSuccess { mediaController = it }
            }
        }, directExecutor)
    }

    override fun onStop() {
        controllerFuture?.let { MediaController.releaseFuture(it) }
        controllerFuture = null
        mediaController = null
        super.onStop()
    }

    override fun onDestroy() {
        discAnimator?.cancel()
        discAnimator = null
        super.onDestroy()
    }

    private fun showTab(itemId: Int) {
        popToTabRoot()
        val tag = "tab_$itemId"
        val manager = supportFragmentManager
        val transaction = manager.beginTransaction().setReorderingAllowed(true)
        manager.fragments.forEach { transaction.hide(it) }
        val existing = manager.findFragmentByTag(tag)
        if (existing != null) {
            transaction.show(existing)
        } else {
            transaction.add(R.id.fragmentContainer, createTab(itemId), tag)
        }
        transaction.commitNow()
    }

    private fun createTab(itemId: Int): Fragment = when (itemId) {
        R.id.nav_device -> DeviceLibraryFragment()
        R.id.nav_live -> LiveFragment()
        R.id.nav_search -> PlaceholderFragments.SearchFragment()
        R.id.nav_library -> PlaceholderFragments.LibraryFragment()
        R.id.nav_settings -> SettingsFragment()
        else -> HomeFragment()
    }

    private fun isLandscape(): Boolean =
        resources.configuration.orientation == Configuration.ORIENTATION_LANDSCAPE

    private fun bindLandscapeBar() {
        binding.landDevice.setOnClickListener { selectTab(R.id.nav_device) }
        binding.landMenu.setOnClickListener {
            playerMenu = !playerMenu
            refreshLandscapeIcons()
        }
        binding.landSlot1.setOnClickListener { onLandSlot(1) }
        binding.landSlot2.setOnClickListener { onLandSlot(2) }
        binding.landSlot3.setOnClickListener { onLandSlot(3) }
        binding.landSlot4.setOnClickListener { onLandSlot(4) }
        binding.landSlot5.setOnClickListener { onLandSlot(5) }
    }

    private fun onLandSlot(slot: Int) {
        if (playerMenu) {
            when (slot) {
                1 -> PlaybackManager.toggleRepeat()
                2 -> DeviceQueue.previous(this)
                3 -> togglePlayback()
                4 -> DeviceQueue.next(this)
                5 -> PlaybackManager.toggleShuffle()
            }
            refreshLandscapeIcons()
            return
        }
        val tab = when (slot) {
            1 -> R.id.nav_home
            2 -> R.id.nav_live
            3 -> R.id.nav_search
            4 -> R.id.nav_library
            else -> R.id.nav_settings
        }
        selectTab(tab)
    }

    private fun selectTab(tabId: Int) {
        currentTab = tabId
        showTab(tabId)
        refreshLandscapeIcons()
        if (!isLandscape() && binding.bottomNav.menu.findItem(tabId) != null) {
            binding.bottomNav.selectedItemId = tabId
        }
    }

    private fun applyOrientationUi(animateMini: Boolean = true) {
        val landscape = isLandscape()
        if (!landscape) playerMenu = false
        binding.bottomNav.isVisible = !landscape
        binding.landscapeBar.isVisible = landscape
        if (landscape) {
            refreshLandscapeIcons()
            updateDiscArtwork(PlayerState.playbackInfo.value.artwork, PlayerState.playbackInfo.value.visible)
            syncDiscRotation(PlayerState.uiState.value.playbackState)
            setMiniPlayerVisible(false, animateMini)
        } else {
            if (currentTab == R.id.nav_device) {
                currentTab = R.id.nav_home
                showTab(R.id.nav_home)
            }
            if (binding.bottomNav.menu.findItem(currentTab) != null) {
                binding.bottomNav.selectedItemId = currentTab
            }
            stopDiscRotation()
            setMiniPlayerVisible(miniPlayerWantsVisible, animateMini)
        }
    }

    private fun refreshLandscapeIcons() {
        if (!isLandscape()) return
        tintButton(binding.landDevice, R.drawable.ic_nav_folder, currentTab == R.id.nav_device)
        tintButton(binding.landMenu, R.drawable.ic_nav_menu, playerMenu)

        if (playerMenu) {
            val state = PlayerState.uiState.value
            val playing = state.playbackState == PlaybackState.PLAYING
            tintButton(
                binding.landSlot1,
                if (state.repeatMode == Player.REPEAT_MODE_ONE) R.drawable.ic_player_repeat_one
                else R.drawable.ic_player_repeat,
                state.repeatMode != Player.REPEAT_MODE_OFF
            )
            tintButton(binding.landSlot2, R.drawable.ic_player_previous, true)
            tintButton(binding.landSlot3, if (playing) R.drawable.ic_pause else R.drawable.ic_play, true)
            tintButton(binding.landSlot4, R.drawable.ic_player_next, true)
            tintButton(binding.landSlot5, R.drawable.ic_player_shuffle, state.shuffle)
            binding.landSlot1.contentDescription = getString(R.string.nav_repeat)
            binding.landSlot2.contentDescription = getString(R.string.nav_previous)
            binding.landSlot3.contentDescription = getString(R.string.play_pause)
            binding.landSlot4.contentDescription = getString(R.string.nav_next)
            binding.landSlot5.contentDescription = getString(R.string.nav_shuffle)
        } else {
            tintButton(binding.landSlot1, R.drawable.ic_nav_home, currentTab == R.id.nav_home)
            tintButton(binding.landSlot2, R.drawable.ic_nav_live, currentTab == R.id.nav_live)
            tintButton(binding.landSlot3, R.drawable.ic_nav_search, currentTab == R.id.nav_search)
            tintButton(binding.landSlot4, R.drawable.ic_nav_library, currentTab == R.id.nav_library)
            tintButton(binding.landSlot5, R.drawable.ic_nav_settings, currentTab == R.id.nav_settings)
            binding.landSlot1.contentDescription = getString(R.string.nav_home)
            binding.landSlot2.contentDescription = getString(R.string.nav_live)
            binding.landSlot3.contentDescription = getString(R.string.nav_search)
            binding.landSlot4.contentDescription = getString(R.string.nav_library)
            binding.landSlot5.contentDescription = getString(R.string.nav_settings)
        }
    }

    private fun tintButton(button: ImageButton, icon: Int, active: Boolean) {
        button.setImageDrawable(
            ContextCompat.getDrawable(this, icon)?.mutate()?.apply {
                setTint(if (active) Color.WHITE else 0x80FFFFFF.toInt())
            }
        )
    }

    private fun updateDiscArtwork(artwork: Any?, hasSession: Boolean) {
        if (!hasSession || artwork == null) {
            binding.landDisc.setImageResource(R.mipmap.ic_launcher)
            binding.landDisc.rotation = 0f
            return
        }
        Glide.with(binding.landDisc)
            .load(artwork)
            .placeholder(R.mipmap.ic_launcher)
            .error(R.mipmap.ic_launcher)
            .circleCrop()
            .into(binding.landDisc)
    }

    private fun syncDiscRotation(state: PlaybackState) {
        if (!isLandscape()) {
            stopDiscRotation()
            return
        }
        if (state == PlaybackState.PLAYING) startDiscRotation() else stopDiscRotation()
    }

    private fun startDiscRotation() {
        if (discAnimator == null) {
            discAnimator = ObjectAnimator.ofFloat(binding.landDisc, View.ROTATION, 0f, 360f).apply {
                duration = 12_000L
                repeatCount = ValueAnimator.INFINITE
                interpolator = LinearInterpolator()
                start()
                pause()
            }
        }
        discAnimator?.resume()
    }

    private fun stopDiscRotation() {
        discAnimator?.pause()
    }

    private fun setMiniPlayerVisible(show: Boolean, animate: Boolean) {
        val view = binding.miniPlayer
        view.animate().cancel()
        if (!animate) {
            view.isVisible = show
            view.alpha = 1f
            view.translationY = 0f
            return
        }
        if (show) {
            if (view.isVisible && view.alpha >= 0.99f) return
            view.isVisible = true
            val distance = if (view.height > 0) view.height.toFloat() else 56f * resources.displayMetrics.density
            view.alpha = 0f
            view.translationY = distance
            view.animate()
                .alpha(1f)
                .translationY(0f)
                .setDuration(220L)
                .start()
        } else {
            if (!view.isVisible) return
            val distance = if (view.height > 0) view.height.toFloat() else 56f * resources.displayMetrics.density
            view.animate()
                .alpha(0f)
                .translationY(distance)
                .setDuration(220L)
                .withEndAction {
                    view.isVisible = false
                    view.alpha = 1f
                    view.translationY = 0f
                }
                .start()
        }
    }

    private fun updateProgressBar(position: Long, duration: Long) {
        val bar = binding.playbackProgress
        if (duration <= 0L) {
            bar.progress = 0
            return
        }
        bar.progress = ((position.coerceAtLeast(0L) * 1000L) / duration).toInt().coerceIn(0, 1000)
    }

    private fun openPlayerScreen() {
        val info = PlayerState.playbackInfo.value
        if (!info.visible) return
        if (info.mediaType == MediaType.VIDEO) {
            startActivity(Intent(this, VideoActivity::class.java))
            return
        }
        if (supportFragmentManager.findFragmentByTag(TAG_NOW_PLAYING) != null) return
        supportFragmentManager.beginTransaction()
            .setReorderingAllowed(true)
            .add(R.id.fragmentContainer, NowPlayingFragment(), TAG_NOW_PLAYING)
            .addToBackStack(TAG_NOW_PLAYING)
            .commit()
    }

    private fun togglePlayback() {
        val controller = mediaController
        if (controller == null) PlayerManager.toggle()
        else if (controller.isPlaying) controller.pause()
        else controller.play()
    }

    private fun popToTabRoot() {
        supportFragmentManager.popBackStackImmediate(null, FragmentManager.POP_BACK_STACK_INCLUSIVE)
    }

    fun openSeeAll(sectionId: String) {
        if (supportFragmentManager.backStackEntryCount > 0) return
        val current = supportFragmentManager.fragments.firstOrNull { it.isVisible } ?: return
        supportFragmentManager.beginTransaction()
            .setReorderingAllowed(true)
            .hide(current)
            .add(R.id.fragmentContainer, SeeAllFragment.newInstance(sectionId))
            .addToBackStack(null)
            .commit()
    }

    private fun bindMiniPlayer() {
        binding.miniPlayPause.setOnClickListener {
            val controller = mediaController
            if (controller == null) {
                PlayerManager.toggle()
            } else if (controller.isPlaying) {
                controller.pause()
            } else {
                controller.play()
            }
        }
        binding.miniPlayer.setOnClickListener { openPlayerScreen() }

        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    PlayerState.playbackInfo.collect { info ->
                        miniPlayerWantsVisible = info.visible
                        binding.miniTitle.text = info.title
                        Glide.with(binding.miniArtwork)
                            .load(info.artwork)
                            .placeholder(R.drawable.bg_artwork_placeholder)
                            .error(R.drawable.bg_artwork_placeholder)
                            .centerCrop()
                            .into(binding.miniArtwork)
                        if (isLandscape()) {
                            updateDiscArtwork(info.artwork, info.visible)
                            setMiniPlayerVisible(false, animate = true)
                        } else {
                            setMiniPlayerVisible(info.visible, animate = true)
                        }
                    }
                }
                launch {
                    PlayerState.uiState.collect { state ->
                        val playing = state.playbackState == PlaybackState.PLAYING
                        binding.miniPlayPause.setImageResource(
                            if (playing) R.drawable.ic_pause else R.drawable.ic_play
                        )
                        updateProgressBar(state.position, state.duration)
                        if (isLandscape()) {
                            syncDiscRotation(state.playbackState)
                            if (playerMenu) refreshLandscapeIcons()
                        }
                    }
                }
            }
        }
    }

    private companion object {
        const val KEY_TAB = "current_tab"
        const val KEY_PLAYER_MENU = "player_menu"
        const val TAG_NOW_PLAYING = "now_playing"
    }
}
