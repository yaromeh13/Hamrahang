package ir.hamrahang.app

import android.content.ComponentName
import android.content.Intent
import android.content.res.Configuration
import android.graphics.Color
import android.os.Bundle
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
    private var suppressMenuCallback = false

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        // No-op when unchanged; otherwise AppCompat recreates the activity.
        AppCompatDelegate.setApplicationLocales(
            LocaleListCompat.forLanguageTags(AppState.language)
        )

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // BottomNavigationView handles the bottom inset itself.
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
            if (suppressMenuCallback) return@setOnItemSelectedListener true
            onNavigationItem(item.itemId, reselected = false)
        }
        binding.bottomNav.setOnItemReselectedListener { item ->
            if (!suppressMenuCallback) onNavigationItem(item.itemId, reselected = true)
        }

        if (savedInstanceState == null) showTab(R.id.nav_home)
        renderMenu()
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

    /** Tabs are hidden rather than replaced, so each keeps its scroll position. */
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

    private fun onNavigationItem(itemId: Int, reselected: Boolean): Boolean {
        when (itemId) {
            R.id.nav_player_toggle -> {
                playerMenu = !playerMenu
                renderMenu()
            }
            R.id.nav_repeat -> PlaybackManager.toggleRepeat()
            R.id.nav_previous -> DeviceQueue.previous(this)
            R.id.nav_play -> togglePlayback()
            R.id.nav_next -> DeviceQueue.next(this)
            R.id.nav_shuffle -> PlaybackManager.toggleShuffle()
            else -> {
                currentTab = itemId
                if (reselected) popToTabRoot() else showTab(itemId)
                return true
            }
        }
        refreshPlayerIcons()
        return false
    }

    private fun renderMenu() {
        if (!isLandscape()) playerMenu = false
        suppressMenuCallback = true
        binding.bottomNav.menu.clear()
        binding.bottomNav.inflateMenu(
            when {
                !isLandscape() -> R.menu.bottom_nav
                playerMenu -> R.menu.bottom_nav_player
                else -> R.menu.bottom_nav_landscape
            }
        )
        if (binding.bottomNav.menu.findItem(currentTab) == null) {
            currentTab = R.id.nav_home
            showTab(currentTab)
        }
        val selected = if (playerMenu) R.id.nav_player_toggle else currentTab
        if (binding.bottomNav.menu.findItem(selected) != null) {
            binding.bottomNav.selectedItemId = selected
        }
        suppressMenuCallback = false
        refreshPlayerIcons()
    }

    private fun refreshPlayerIcons() {
        if (!playerMenu) {
            binding.bottomNav.itemIconTintList =
                ContextCompat.getColorStateList(this, R.color.bottom_nav_icon_tint)
            return
        }
        binding.bottomNav.itemIconTintList = null
        val state = PlayerState.uiState.value
        val playing = state.playbackState == PlaybackState.PLAYING
        tint(R.id.nav_device, R.drawable.ic_nav_folder, false)
        tint(
            R.id.nav_repeat,
            if (state.repeatMode == Player.REPEAT_MODE_ONE) R.drawable.ic_player_repeat_one
            else R.drawable.ic_player_repeat,
            state.repeatMode != Player.REPEAT_MODE_OFF
        )
        tint(R.id.nav_previous, R.drawable.ic_player_previous, true)
        tint(R.id.nav_play, if (playing) R.drawable.ic_pause else R.drawable.ic_play, true)
        tint(R.id.nav_next, R.drawable.ic_player_next, true)
        tint(R.id.nav_shuffle, R.drawable.ic_player_shuffle, state.shuffle)
        tint(R.id.nav_player_toggle, R.drawable.ic_nav_menu, true)
    }

    private fun tint(itemId: Int, icon: Int, active: Boolean) {
        val item = binding.bottomNav.menu.findItem(itemId) ?: return
        item.icon = ContextCompat.getDrawable(this, icon)?.mutate()?.apply {
            setTint(if (active) Color.WHITE else 0x80FFFFFF.toInt())
        }
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
        binding.miniPlayer.setOnClickListener {
            if (PlayerState.playbackInfo.value.mediaType == MediaType.VIDEO) {
                startActivity(Intent(this, VideoActivity::class.java))
            }
        }

        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    PlayerState.playbackInfo.collect { info ->
                        binding.miniPlayer.isVisible = info.visible
                        binding.miniTitle.text = info.title
                        Glide.with(binding.miniArtwork)
                            .load(info.artwork)
                            .placeholder(R.drawable.bg_artwork_placeholder)
                            .error(R.drawable.bg_artwork_placeholder)
                            .centerCrop()
                            .into(binding.miniArtwork)
                    }
                }
                launch {
                    PlayerState.uiState.collect { state ->
                        val playing = state.playbackState == PlaybackState.PLAYING
                        binding.miniPlayPause.setImageResource(
                            if (playing) R.drawable.ic_pause else R.drawable.ic_play
                        )
                        refreshPlayerIcons()
                    }
                }
            }
        }
    }

    private companion object {
        const val KEY_TAB = "current_tab"
        const val KEY_PLAYER_MENU = "player_menu"
    }
}
