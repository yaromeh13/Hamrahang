package ir.hamrahang.tv

import android.content.Context
import android.content.Intent
import android.util.Log
import android.view.View
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.contract.Focusable
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.ui.*
import ir.hamrahang.tv.databinding.ActivityMainBinding
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.network.NetworkEvents
import ir.hamrahang.tv.network.NetworkMonitor
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.ui.message.MessageWidget
import ir.hamrahang.tv.ui.settings.LanguageManager
import ir.hamrahang.tv.ui.settings.SettingsFragment
import ir.hamrahang.tv.utils.AppState
import ir.hamrahang.tv.channel.HomeChannel
import ir.hamrahang.tv.channel.HomeChannelPublisher
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.auth.AuthSessionManager
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import kotlinx.coroutines.launch
import kotlinx.coroutines.Job
import androidx.activity.OnBackPressedCallback

class MainActivity : BaseActivity(),
    NavFragment.NavCallback {
    private lateinit var binding: ActivityMainBinding
    private var lastBackPressTime: Long = 0L
    private var isPlayerFullscreen = false
    private var recoverySyncJob: Job? = null
    private var apiReachable = false

    override fun onCreate(savedInstanceState: android.os.Bundle?) {
        super.onCreate(savedInstanceState)

        if (AppState.offlineMode) {
            Log.d("OFFLINE", "Offline Mode")
        } else {
            // SplashActivity already completed a successful API ping.
            apiReachable = true
        }

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Keep messages visible across Home, detail screens and the player.
        MessageWidget(binding.root, this)

        UiNavigator.init(this)

        // Restore the account quietly in the background; local data stays usable offline.
        lifecycleScope.launch {
            AuthSessionManager.refreshSession(this@MainActivity)
        }

        lifecycleScope.launchWhenStarted {
            NetworkEvents.events.collect { event ->
                when (event) {
                    // A single request timeout does not prove that the whole API is offline.
                    // In particular, never interrupt the current screen with a retry overlay.
                    NetworkEvents.Event.Timeout -> Unit

                    NetworkEvents.Event.InternetAvailable -> {
                        MessageManager.info("Internet Connected")
                        HomeChannelPublisher.publish(this@MainActivity)

                        // NetworkMonitor only reports device connectivity. Confirm the
                        // actual Hamrahang API before starting background synchronization.
                        if (!apiReachable && recoverySyncJob?.isActive != true) {
                            recoverySyncJob = lifecycleScope.launch {
                                val apiIsReady = runCatching {
                                    val response = ApiClient.api.ping()
                                    response.isSuccessful &&
                                        response.body()?.status == "ok"
                                }.getOrDefault(false)

                                if (!apiIsReady) {
                                    Log.w("NETWORK_RECOVERY", "Hamrahang API is still unavailable")
                                    return@launch
                                }

                                apiReachable = true

                                runCatching {
                                    AuthSessionManager.refreshSession(this@MainActivity)
                                }

                                val synced = runCatching {
                                    PlaylistRepository(this@MainActivity).refreshAndSync()
                                }.getOrDefault(false)

                                if (synced) {
                                    (supportFragmentManager.findFragmentById(R.id.contentContainer) as? HomeFragment)
                                        ?.refreshPlaylists()
                                }
                            }
                        }
                    }

                    NetworkEvents.Event.InternetLost -> {
                        apiReachable = false
                        MessageManager.warning("Internet Disconnected")
                    }
                }
            }
        }

        val firstFragment =
            if (AppState.restoreSettingsLanguage) {
                AppState.restoreSettingsLanguage = false
                SettingsFragment.newLanguagePage()
            } else {
                HomeFragment()
            }

        supportFragmentManager.beginTransaction()
            .replace(R.id.nav_fragment, NavFragment())
            .replace(R.id.contentContainer, firstFragment)
            .commit()
        openFromChannel(intent)

        // BACK HANDLING
        onBackPressedDispatcher.addCallback(
            this,
            object : OnBackPressedCallback(true) {

                override fun handleOnBackPressed() {

                    val fm = supportFragmentManager

                    // اگر BackStack داریم
                    if (fm.backStackEntryCount > 0) {
                        UiNavigator.onBack()
                        return
                    }

                    // BackStack خالی است
                    // منطق خروج با دوبار Back

                    val now = System.currentTimeMillis()

                    if (now - lastBackPressTime < 3000L) {
                        // این Callback را غیرفعال می‌کنیم
                        // تا Back به Activity/System برسد
                        isEnabled = false
                        onBackPressedDispatcher.onBackPressed()
                    } else {
                        lastBackPressTime = now
                        MessageManager.warning(
                            "برای خروج دوباره بک را بزنید"
                        )
                    }
                }
            }
        )

    }

    override fun onMenuSelected(menuId: Int) {
        supportFragmentManager.popBackStack(null, androidx.fragment.app.FragmentManager.POP_BACK_STACK_INCLUSIVE)

        val fragment = when (menuId) {
            R.id.menuHome -> HomeFragment()
            R.id.menuLive -> LiveFragment()
            R.id.menuSearch -> SearchFragment()
            R.id.menuSettings -> SettingsFragment()
            else -> HomeFragment()
        }

        supportFragmentManager.beginTransaction()
            .replace(R.id.contentContainer, fragment)
            .commit()
    }

    override fun onRequestMainFocus() {
        val fragment = supportFragmentManager.findFragmentById(R.id.contentContainer)
        fragment?.view?.post { (fragment as? Focusable)?.requestInitialFocus() }
    }

    override fun attachBaseContext(newBase: Context) {
        super.attachBaseContext(
            LanguageManager.applyLanguage(newBase)
        )
    }

    fun onReturnToMenu() {
        val navFragment = supportFragmentManager.findFragmentById(R.id.nav_fragment)
        val navView = navFragment?.view

        navView?.post {
            navView.findViewById<View>(R.id.menuHome)?.requestFocus()
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        openFromChannel(intent)
    }

    private fun openFromChannel(source: Intent?) {
        val typeKey = source?.getStringExtra(HomeChannel.EXTRA_TYPE) ?: return
        val id = source.getStringExtra(HomeChannel.EXTRA_ID) ?: return
        source.removeExtra(HomeChannel.EXTRA_TYPE)
        source.removeExtra(HomeChannel.EXTRA_ID)
        val type = ContentType.values().firstOrNull { it.apiKey == typeKey } ?: return
        supportFragmentManager.executePendingTransactions()
        binding.root.post {
            UiNavigator.openMediaDetail(type, id)
        }
    }

    override fun onStart() {
        super.onStart()
        BackgroundManager.attachViews(binding.globalBgImage, binding.globalBgDim)
    }

    override fun onStop() {
        super.onStop()
        BackgroundManager.detachViews()
    }

    // ==================================================
    // 👇 مدیریت لایه فول‌اسکرین پلیر
    // ==================================================

    fun openFullscreenPlayer(fragment: Fragment) {
        binding.fullscreenPlayerContainer.visibility = View.VISIBLE
        isPlayerFullscreen = true

        binding.navFragment.visibility = View.GONE
        binding.contentContainer.visibility = View.GONE

        supportFragmentManager.beginTransaction()
            .replace(R.id.fullscreenPlayerContainer, fragment)
            .commit()

        hideSystemUI()
    }

    fun closeFullscreenPlayer() {
        val playerFragment = supportFragmentManager.findFragmentById(R.id.fullscreenPlayerContainer)
        playerFragment?.let {
            supportFragmentManager.beginTransaction().remove(it).commit()
        }
        binding.fullscreenPlayerContainer.visibility = View.GONE
        isPlayerFullscreen = false

        binding.navFragment.visibility = View.VISIBLE
        binding.contentContainer.visibility = View.VISIBLE

        PlayerState.hideFullscreen()
        onRequestMainFocus()
    }

}
