package ir.hamrahang.tv.ui.settings

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.View
import android.view.inputmethod.EditorInfo
import android.graphics.BitmapFactory
import android.util.Base64
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.TextView
import android.widget.FrameLayout
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.settings.QualityLevel
import ir.hamrahang.tv.settings.SettingsManager
import com.bumptech.glide.Glide
import ir.hamrahang.tv.util.UiUtils
import ir.hamrahang.tv.utils.AppState
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryRepository
import ir.hamrahang.tv.data.local.SearchHistoryStore
import ir.hamrahang.tv.data.auth.AuthRequest
import ir.hamrahang.tv.data.auth.AuthResponse
import ir.hamrahang.tv.data.auth.AuthSessionStore
import ir.hamrahang.tv.data.auth.AuthSessionManager
import ir.hamrahang.tv.data.favorite.AccountSyncRepository
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.ui.message.MessageManager
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch


class SettingsFragment : Fragment(R.layout.fragment_settings), ir.hamrahang.tv.contract.Focusable {

    private lateinit var categoryAdapter: SettingsCategoryAdapter
    private lateinit var optionAdapter: SettingsOptionAdapter
    private lateinit var categoryRecycler: RecyclerView
    private lateinit var optionsRecycler: RecyclerView
    private lateinit var contentContainer: FrameLayout
    private lateinit var languageAdapter: LanguageAdapter
    private lateinit var languageRecycler: RecyclerView
    private var qrPollJob: Job? = null
    private var selectedCategoryIndex: Int = 0
    private val accountSyncRepository by lazy { AccountSyncRepository(requireContext()) }

    private val playbackHistoryRepository by lazy {

        PlaybackHistoryRepository(
            DatabaseProvider
                .database()
                .playbackHistoryDao()
        )

    }

    private val categories = listOf(

        // Profile
        SettingCategory(
            "Profile",
            R.drawable.ic_profile
        ),

        // Settings
        SettingCategory(
            "Audio",
            R.drawable.ic_volume_up
        ),

        SettingCategory(
            "Video",
            R.drawable.ic_video
        ),

        SettingCategory(
            "General",
            R.drawable.ic_general
        ),

        SettingCategory(
            "Theme",
            R.drawable.ic_theme
        ),

        SettingCategory(
            "language",
            R.drawable.ic_language
        ),

    )

    override fun onViewCreated(
        view: View,
        savedInstanceState: Bundle?
    ) {
        super.onViewCreated(view, savedInstanceState)
        categoryRecycler = view.findViewById(R.id.categoryRecycler)
        optionsRecycler = view.findViewById(R.id.optionsRecycler)
        contentContainer = view.findViewById(R.id.settingsContentContainer)
        categoryRecycler.layoutManager =
            LinearLayoutManager(
                requireContext(),
                LinearLayoutManager.HORIZONTAL,
                false
            )
        optionsRecycler.layoutManager = LinearLayoutManager(requireContext())
        categoryAdapter = SettingsCategoryAdapter(categories) {loadCategory(it)}
        optionAdapter =
            SettingsOptionAdapter(
                mutableListOf()
            ) { option ->

                when (option.id) {
                    "audio_high" -> SettingsManager.saveAudioQuality(QualityLevel.HIGH)
                    "audio_medium" -> SettingsManager.saveAudioQuality(QualityLevel.MEDIUM)
                    "audio_low" -> SettingsManager.saveAudioQuality(QualityLevel.LOW)
                    "video_high" -> SettingsManager.saveVideoQuality(QualityLevel.HIGH)
                    "video_medium" -> SettingsManager.saveVideoQuality(QualityLevel.MEDIUM)
                    "video_low" -> SettingsManager.saveVideoQuality(QualityLevel.LOW)
                    // Local-only cleanup: never call account APIs from these actions.
                    "clean_recent" -> runLocalCleanup(R.string.clean_recent_success) {
                        playbackHistoryRepository.clear()
                    }

                    "clean_search" -> runLocalCleanup(R.string.clean_search_success) {
                        SearchHistoryStore.clear(requireContext())
                    }

                    "clean_favorites" -> runLocalCleanup(R.string.clean_favorites_success) {
                        DatabaseProvider.database().favoriteDao().clearAll()
                    }

                }
            }

        categoryRecycler.adapter = categoryAdapter
        optionsRecycler.adapter = optionAdapter

        languageRecycler = view.findViewById(R.id.languageRecycler)
        languageRecycler.layoutManager =
            GridAutoFitLayoutManager(
                requireContext(),
                UiUtils.dp(
                    requireContext(),
                    170
                )
            )
        languageAdapter = LanguageAdapter(
            listOf(
                LanguageItem("fa","فارسی",R.drawable.flag_ir),
                LanguageItem("en","English",R.drawable.flag_en),
                //LanguageItem("ar","العربية",R.drawable.flag_sa),
                //LanguageItem("fr","Français",R.drawable.flag_fr),
                //LanguageItem("de","Deutsch",R.drawable.flag_de),
                )
        ) { language ->

            //val current = LanguageManager.getLanguage(requireContext())
            val current = LanguageManager.getLanguage()
            if (current == language.code) return@LanguageAdapter

            AppState.restoreSettingsLanguage = true
            SettingsManager.saveLanguage(language.code)
            /*LanguageManager.setLanguage(
                requireContext(),
                language.code
            )*/
            LanguageManager.restartApp(requireContext())
            //restartApp(requireContext())
            //requireActivity().recreate()

        }

        languageRecycler.adapter =
            languageAdapter

        languageRecycler.post {

            //val current = LanguageManager.getLanguage(requireContext())
            val current = LanguageManager.getLanguage()
            val index = languageAdapter.indexOf(current)

            if (index >= 0) {
                languageRecycler.scrollToPosition(index)
                languageRecycler.post {
                    languageRecycler
                        .findViewHolderForAdapterPosition(index)
                        ?.itemView
                        ?.requestFocus()
                }
            }
        }

        loadCategory(0)

        if (arguments?.getBoolean(ARG_LANGUAGE) == true) {
            categoryRecycler.post {
                categoryRecycler.scrollToPosition(5)

            }
            loadCategory(5)
        }
    }

    override fun requestInitialFocus() {
        if (!::categoryRecycler.isInitialized) return
        categoryRecycler.post {
            if (!isAdded) return@post
            categoryRecycler.scrollToPosition(0)
            categoryRecycler.post {
                val firstCategory = categoryRecycler
                    .findViewHolderForAdapterPosition(0)
                    ?.itemView
                if (firstCategory?.requestFocus() != true) {
                    categoryRecycler.requestFocus()
                }
            }
        }
    }

    private fun restartApp(context: Context) {

        val intent =
            requireActivity().packageManager
                .getLaunchIntentForPackage(context.packageName)
                ?: return

        intent.addFlags(
            android.content.Intent.FLAG_ACTIVITY_NEW_TASK or
                    android.content.Intent.FLAG_ACTIVITY_CLEAR_TASK
        )

        context.startActivity(intent)
        requireActivity().finish()
    }
    companion object {

        private const val ARG_LANGUAGE = "language"
        fun newLanguagePage(): SettingsFragment {
            return SettingsFragment().apply {
                arguments = Bundle().apply {
                    putBoolean(ARG_LANGUAGE, true)
                }
            }
        }
    }
    private fun showProfile() {
        contentContainer.removeAllViews()
        contentContainer.visibility = View.VISIBLE
        val root = layoutInflater.inflate(R.layout.view_profile_info, contentContainer, true)
        renderAccountState(root)
        setupLoginInputNavigation(root)

        setupAuthButtonFocus(root.findViewById(R.id.loginButton))
        setupAuthButtonFocus(root.findViewById(R.id.qrRetryButton))
        setupAuthButtonFocus(root.findViewById(R.id.logoutButton))
        setupAuthButtonFocus(root.findViewById(R.id.syncProfileButton))

        root.findViewById<Button>(R.id.loginButton).setOnClickListener {
            val username = root.findViewById<EditText>(R.id.emailInput).text.toString().trim()
            val password = root.findViewById<EditText>(R.id.passwordInput).text.toString()
            val error = root.findViewById<TextView>(R.id.loginError)
            if (username.isBlank() || password.isBlank()) {
                error.setText(R.string.auth_missing_credentials)
                error.visibility = View.VISIBLE
            } else {
                performPasswordLogin(root, username, password)
            }
        }

        root.findViewById<Button>(R.id.qrRetryButton).setOnClickListener {
            startQrLogin(root)
        }
        root.findViewById<Button>(R.id.logoutButton).setOnClickListener {
            logout(root)
        }
        root.findViewById<Button>(R.id.syncProfileButton).apply {
            visibility = if (AuthSessionStore.isLoggedIn(requireContext())) View.VISIBLE else View.GONE
            setOnClickListener { performManualProfileSync(this) }
        }

        if (!AuthSessionStore.isLoggedIn(requireContext())) {
            startQrLogin(root)
        } else {
            viewLifecycleOwner.lifecycleScope.launch {
                AuthSessionManager.refreshSession(requireContext())
                if (isAdded && root.parent != null) {
                    renderAccountState(root)
                    if (!AuthSessionStore.isLoggedIn(requireContext())) startQrLogin(root)
                }
            }
        }
    }

    private fun setupLoginInputNavigation(root: View) {
        val usernameInput = root.findViewById<EditText>(R.id.emailInput)
        val passwordInput = root.findViewById<EditText>(R.id.passwordInput)
        val loginButton = root.findViewById<Button>(R.id.loginButton)

        // Hardware/IME action keys move focus forward without trapping it in an EditText.
        usernameInput.setOnEditorActionListener { _, actionId, _ ->
            if (actionId == EditorInfo.IME_ACTION_NEXT) {
                passwordInput.requestFocus()
                true
            } else {
                false
            }
        }

        passwordInput.setOnEditorActionListener { _, actionId, _ ->
            if (actionId == EditorInfo.IME_ACTION_DONE) {
                loginButton.requestFocus()
                true
            } else {
                false
            }
        }
    }

    private fun setupAuthButtonFocus(button: Button) {
        button.setBackgroundResource(R.drawable.bg_setting_option)
        button.backgroundTintList = null
        button.setOnFocusChangeListener { view, hasFocus ->
            val scale = if (hasFocus) 1.04f else 1f
            view.animate()
                .scaleX(scale)
                .scaleY(scale)
                .setDuration(160)
                .start()
        }
    }

    private fun renderAccountState(root: View) {
        val logged = AuthSessionStore.isLoggedIn(requireContext())
        ir.hamrahang.tv.favorite.FavoriteManager.setAccessToken(
            AuthSessionStore.accessToken(requireContext())
        )
        root.findViewById<View>(R.id.layoutLogged).visibility = if (logged) View.VISIBLE else View.GONE
        root.findViewById<View>(R.id.layoutLogin).visibility = if (logged) View.GONE else View.VISIBLE
        if (logged) {
            val context = requireContext()
            root.findViewById<TextView>(R.id.accountName).text =
                AuthSessionStore.displayName(context)
            root.findViewById<TextView>(R.id.accountUsername).text =
                getString(R.string.auth_username_value, AuthSessionStore.username(context))
            root.findViewById<TextView>(R.id.accountEmail).text =
                getString(R.string.auth_email_value, AuthSessionStore.email(context))
            root.findViewById<TextView>(R.id.accountId).text =
                getString(R.string.auth_user_id_value, AuthSessionStore.userId(context))
            val avatar = root.findViewById<ImageView>(R.id.userAvatar)
            val avatarUrl = AuthSessionStore.avatarUrl(context)
            if (avatarUrl.isNullOrBlank()) {
                avatar.setImageResource(R.drawable.ic_profile)
                avatar.scaleType = ImageView.ScaleType.CENTER_INSIDE
            } else {
                avatar.scaleType = ImageView.ScaleType.CENTER_CROP
                Glide.with(avatar)
                    .load(avatarUrl)
                    .circleCrop()
                    .placeholder(R.drawable.ic_profile)
                    .error(R.drawable.ic_profile)
                    .into(avatar)
            }
        }
    }

    private fun performPasswordLogin(root: View, username: String, password: String) {
        val error = root.findViewById<TextView>(R.id.loginError)
        val button = root.findViewById<Button>(R.id.loginButton)
        button.isEnabled = false
        error.visibility = View.GONE
        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val response = ApiClient.api.authAction(
                    body = AuthRequest(
                        a = "login_password",
                        username = username,
                        password = password,
                        did = AuthSessionStore.deviceId(requireContext()),
                        device_type = "tv_webapp"
                    )
                )
                if (response.access_token.isNullOrBlank()) {
                    error.text = authErrorMessage(response.error)
                    error.visibility = View.VISIBLE
                    return@launch
                }
                onLoginSuccess(root, response, fromQr = false)
            } catch (_: Exception) {
                error.setText(R.string.auth_network_error)
                error.visibility = View.VISIBLE
            } finally {
                button.isEnabled = true
            }
        }
    }

    private fun startQrLogin(root: View) {
        qrPollJob?.cancel()
        val status = root.findViewById<TextView>(R.id.qrInfoPanel)
        val qr = root.findViewById<ImageView>(R.id.qrImage)
        status.setText(R.string.auth_qr_loading)
        qr.setImageDrawable(null)
        qrPollJob = viewLifecycleOwner.lifecycleScope.launch {
            try {
                val request = ApiClient.api.authAction(body = AuthRequest(a = "request_code"))
                val pairCode = request.pair_url?.substringAfterLast("/")
                val actualCode = pairCode?.takeIf { it.matches(Regex("\\d{6}")) }
                val qrBase64 = request.qr_base64
                if (qrBase64.isNullOrBlank() || actualCode == null) {
                    status.setText(R.string.auth_qr_failed)
                    return@launch
                }
                val encoded = qrBase64.substringAfter("base64,", qrBase64)
                val imageBytes = Base64.decode(encoded, Base64.DEFAULT)
                val bitmap = BitmapFactory.decodeByteArray(imageBytes, 0, imageBytes.size)
                qr.setImageBitmap(bitmap)
                status.text = getString(R.string.auth_qr_instructions, actualCode)
                repeat(200) {
                    delay(3000)
                    val result = runCatching {
                        ApiClient.api.authAction(
                            body = AuthRequest(
                                a = "check_code",
                                code = actualCode,
                                did = AuthSessionStore.deviceId(requireContext()),
                                device_type = "tv_webapp"
                            )
                        )
                    }.getOrNull() ?: return@repeat
                    if (result.status == "confirmed" && !result.access_token.isNullOrBlank()) {
                        onLoginSuccess(root, result, fromQr = true)
                        return@launch
                    }
                    if (result.status == "expired") {
                        status.setText(R.string.auth_qr_expired)
                        return@launch
                    }
                }
                status.setText(R.string.auth_qr_expired)
            } catch (_: Exception) {
                status.setText(R.string.auth_qr_failed)
            }
        }
    }

    private suspend fun onLoginSuccess(root: View, response: AuthResponse, fromQr: Boolean) {
        if (!fromQr) {
            qrPollJob?.cancel()
            qrPollJob = null
        }
        AuthSessionStore.save(requireContext(), response)
        ir.hamrahang.tv.favorite.FavoriteManager.setAccessToken(response.access_token)

        // QR confirmation returns the basic user fields only. The existing refresh API
        // also returns photo/meta, so fetch those details before rendering the account.
        if (fromQr) {
            AuthSessionManager.refreshSession(requireContext(), force = true)
        }

        renderAccountState(root)
        val token = AuthSessionStore.accessToken(requireContext()) ?: response.access_token.orEmpty()
        if (token.isBlank()) return

        // The forced refresh above already pulled server data for QR login.
        // Password login pulls it here. Both paths remain best-effort.
        if (!fromQr) accountSyncRepository.pullServerData(token)
        if (accountSyncRepository.hasLocalDataToSync()) {
            showAccountSyncPrompt()
        }
    }

    private fun showAccountSyncPrompt(retry: Boolean = false) {
        val message = getString(
            if (retry) R.string.auth_sync_failed else R.string.auth_sync_prompt
        )
        MessageManager.confirm(
            text = message,
            positiveText = getString(if (retry) R.string.auth_sync_retry else R.string.auth_sync_yes),
            negativeText = getString(R.string.auth_sync_no),
            onPositive = {
                if (isAdded) performAccountSync()
            },
            onNegative = {
                // The shared message panel closes itself; local data remains untouched.
            }
        )
    }

    private fun performAccountSync() {
        val token = AuthSessionStore.accessToken(requireContext()) ?: return
        viewLifecycleOwner.lifecycleScope.launch {
            val success = runCatching {
                accountSyncRepository.syncLocalData(token)
            }.getOrDefault(false)

            if (!isAdded) return@launch
            if (success) {
                MessageManager.success(getString(R.string.auth_sync_success))
            } else {
                showAccountSyncPrompt(retry = true)
            }
        }
    }

    private fun logout(root: View) {
        val refresh = AuthSessionStore.refreshToken(requireContext())
        val did = AuthSessionStore.deviceId(requireContext())
        viewLifecycleOwner.lifecycleScope.launch {
            runCatching {
                ApiClient.api.authAction(
                    body = AuthRequest(a = "logout", refresh_token = refresh, did = did)
                )
            }
            AuthSessionStore.clear(requireContext())
            ir.hamrahang.tv.favorite.FavoriteManager.setAccessToken(null)
            renderAccountState(root)
            startQrLogin(root)
        }
    }

    private fun performManualProfileSync(button: Button) {
        if (!AuthSessionStore.isLoggedIn(requireContext())) return
        button.isEnabled = false
        button.setText(R.string.auth_syncing)
        viewLifecycleOwner.lifecycleScope.launch {
            val token = AuthSessionStore.accessToken(requireContext())
            val success = if (token.isNullOrBlank()) false else runCatching {
                accountSyncRepository.syncLocalData(token)
            }.getOrDefault(false)
            if (!isAdded) return@launch
            button.isEnabled = true
            button.setText(R.string.auth_sync_profile)
            if (success) MessageManager.success(getString(R.string.auth_sync_success))
            else MessageManager.warning(getString(R.string.auth_sync_failed))
        }
    }

    private fun authErrorMessage(error: String?): String = when (error) {
        "account_disabled" -> getString(R.string.auth_account_disabled)
        "email_not_verified" -> getString(R.string.auth_email_not_verified)
        "rate_limited" -> getString(R.string.auth_rate_limited)
        "missing_credentials" -> getString(R.string.auth_missing_credentials)
        else -> getString(R.string.auth_invalid_credentials)
    }

    private fun runLocalCleanup(successMessage: Int, action: suspend () -> Unit) {
        viewLifecycleOwner.lifecycleScope.launch {
            try {
                action()
                if (isAdded) MessageManager.success(getString(successMessage))
            } catch (_: Exception) {
                if (isAdded) MessageManager.error(getString(R.string.cleanup_failed))
            }
        }
    }

    private fun showAudioSettings() {
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(
            listOf(
                SettingOption(
                    "audio_high",
                    getString(R.string.quality_high),
                    AppState.audioQuality == QualityLevel.HIGH
                ),
                SettingOption(
                    "audio_medium",
                    getString(R.string.quality_medium),
                    AppState.audioQuality == QualityLevel.MEDIUM
                ),
                SettingOption(
                    "audio_low",
                    getString(R.string.quality_low),
                    AppState.audioQuality == QualityLevel.LOW
                )
            )
        )
    }
    private fun showVideoSettings(){
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(

            listOf(

                SettingOption(
                    "video_high",
                    getString(R.string.quality_high),
                    AppState.videoQuality == QualityLevel.HIGH
                ),

                SettingOption(
                    "video_medium",
                    getString(R.string.quality_medium),
                    AppState.videoQuality == QualityLevel.MEDIUM
                ),

                SettingOption(
                    "video_low",
                    getString(R.string.quality_low),
                    AppState.videoQuality == QualityLevel.LOW
                )

            )

        )
    }
    private fun showGeneralSettings() {
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(
            listOf(
                SettingOption(
                    "general_exit",
                    getString(R.string.general_exit_app),
                    true
                ),
                SettingOption(
                    "general_keep",
                    getString(R.string.general_keep_playing)
                ),
                SettingOption(
                    "clean_recent",
                    getString(R.string.clean_recent)
                ),
                SettingOption(
                    "clean_search",
                    getString(R.string.clean_search)
                ),
                SettingOption(
                    "clean_favorites",
                    getString(R.string.clean_favorites)
                )
            )
        )
    }
    private fun showThemeSettings() {
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(
            listOf(
                SettingOption("theme_dark", "Dark", true),
                SettingOption("theme_oled", "OLED"),
                SettingOption("theme_cinema", "Cinema")
            )
        )
    }
    private fun showLanguageSettings() {
        languageRecycler.visibility = View.VISIBLE
    }
    override fun onDestroyView() {
        qrPollJob?.cancel()
        qrPollJob = null
        super.onDestroyView()
    }

    private fun hideAllContent() {
        qrPollJob?.cancel()
        qrPollJob = null
        optionsRecycler.visibility = View.GONE
        languageRecycler.visibility = View.GONE
        contentContainer.visibility = View.GONE
        contentContainer.removeAllViews()
    }
    private fun loadCategory(index: Int) {
        selectedCategoryIndex = index
        hideAllContent()
        when (index) {

            0 -> showProfile()
            1 -> showAudioSettings()
            2 -> showVideoSettings()
            3 -> showGeneralSettings()
            4 -> showThemeSettings()
            5 -> showLanguageSettings()
        }
    }
}