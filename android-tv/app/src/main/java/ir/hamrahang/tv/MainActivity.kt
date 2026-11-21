package ir.hamrahang.tv

import android.content.Context
import android.util.Log
import android.view.View
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.contract.Focusable
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.ui.*
import ir.hamrahang.tv.databinding.ActivityMainBinding
import ir.hamrahang.tv.network.NetworkEvents
import ir.hamrahang.tv.network.NetworkMonitor
import ir.hamrahang.tv.network.ReloadManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.ui.message.MessageWidget
import ir.hamrahang.tv.ui.network.NetworkOverlay
import ir.hamrahang.tv.ui.settings.LanguageManager
import ir.hamrahang.tv.ui.settings.SettingsFragment
import ir.hamrahang.tv.utils.AppState
import ir.hamrahang.tv.data.auth.AuthRequest
import ir.hamrahang.tv.data.auth.AuthSessionStore
import ir.hamrahang.tv.data.favorite.AccountSyncRepository
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.favorite.FavoriteManager
import kotlinx.coroutines.launch
import androidx.activity.OnBackPressedCallback


class MainActivity : BaseActivity(),
    NavFragment.NavCallback {
    private lateinit var binding: ActivityMainBinding
    private lateinit var networkOverlay: NetworkOverlay
    private var lastBackPressTime: Long = 0L
    private var isPlayerFullscreen = false

    override fun onCreate(savedInstanceState: android.os.Bundle?) {
        super.onCreate(savedInstanceState)

        if (AppState.offlineMode) {
            Log.d("OFFLINE", "Offline Mode")
        }

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Keep messages visible across Home, detail screens and the player.
        MessageWidget(binding.root, this)

        UiNavigator.init(this)

        // Restore the account quietly in the background, like the web CheckLoginStatus flow.
        // Cached local content remains available if the server cannot be reached.
        lifecycleScope.launch {
            if (!AuthSessionStore.isLoggedIn(this@MainActivity)) return@launch
            FavoriteManager.setAccessToken(AuthSessionStore.accessToken(this@MainActivity))
            val refreshToken = AuthSessionStore.refreshToken(this@MainActivity) ?: return@launch
            try {
                val session = ApiClient.api.authAction(
                    body = AuthRequest(
                        a = "refresh_session",
                        refresh_token = refreshToken,
                        did = AuthSessionStore.deviceId(this@MainActivity)
                    )
                )
                if (!session.access_token.isNullOrBlank()) {
                    AuthSessionStore.save(this@MainActivity, session)
                    FavoriteManager.setAccessToken(session.access_token)
                    AccountSyncRepository().pullServerData(session.access_token)
                } else if (session.error == "invalid_session" || session.error == "account_disabled") {
                    AuthSessionStore.clear(this@MainActivity)
                    FavoriteManager.setAccessToken(null)
                }
            } catch (_: Exception) {
                // Offline: keep the cached profile, token and local database untouched.
            }
        }

        networkOverlay = NetworkOverlay(binding.root) {
            ReloadManager.reload()
        }

        lifecycleScope.launchWhenStarted {
            NetworkEvents.events.collect { event ->
                when (event) {
                    NetworkEvents.Event.Timeout -> {
                        networkOverlay.show()
                    }
                    NetworkEvents.Event.InternetAvailable -> {
                        networkOverlay.hide()
                        MessageManager.info("Internet Connected")
                        ReloadManager.reload()
                    }
                    NetworkEvents.Event.InternetLost -> {
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