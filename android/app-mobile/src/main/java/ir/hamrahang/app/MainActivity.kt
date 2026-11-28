package ir.hamrahang.app

import android.content.Intent
import android.content.ComponentName
import android.os.Bundle
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.appcompat.app.AppCompatDelegate
import androidx.core.os.LocaleListCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentManager
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.bumptech.glide.Glide
import androidx.media3.session.MediaController
import androidx.media3.session.SessionToken
import com.google.common.util.concurrent.ListenableFuture
import ir.hamrahang.app.playback.MobilePlaybackService
import ir.hamrahang.app.databinding.ActivityMainBinding
import ir.hamrahang.app.ui.PlaceholderFragments
import ir.hamrahang.app.ui.SettingsFragment
import ir.hamrahang.app.ui.home.HomeFragment
import ir.hamrahang.app.ui.home.SeeAllFragment
import ir.hamrahang.app.ui.player.VideoActivity
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlaybackState
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.flow.distinctUntilChanged
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import java.util.concurrent.Executor

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var controllerFuture: ListenableFuture<MediaController>? = null
    private var mediaController: MediaController? = null
    private val directExecutor = Executor { command -> command.run() }

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

        binding.bottomNav.setOnItemSelectedListener { item ->
            showTab(item.itemId)
            true
        }
        // Tapping the current tab again leaves "See all".
        binding.bottomNav.setOnItemReselectedListener { popToTabRoot() }

        if (savedInstanceState == null) showTab(R.id.nav_home)

        bindMiniPlayer()
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
        R.id.nav_live -> PlaceholderFragments.LiveFragment()
        R.id.nav_search -> PlaceholderFragments.SearchFragment()
        R.id.nav_library -> PlaceholderFragments.LibraryFragment()
        R.id.nav_settings -> SettingsFragment()
        else -> HomeFragment()
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
                    PlayerState.uiState
                        .map { it.playbackState == PlaybackState.PLAYING }
                        .distinctUntilChanged()
                        .collect { playing ->
                            binding.miniPlayPause.setImageResource(
                                if (playing) R.drawable.ic_pause else R.drawable.ic_play
                            )
                        }
                }
            }
        }
    }
}
