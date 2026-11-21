package ir.hamrahang.tv.ui.settings

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.FrameLayout
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.settings.QualityLevel
import ir.hamrahang.tv.settings.SettingsManager
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.util.UiUtils
import ir.hamrahang.tv.utils.AppState
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryRepository
import kotlinx.coroutines.launch


class SettingsFragment : Fragment(R.layout.fragment_settings) {

    private lateinit var categoryAdapter: SettingsCategoryAdapter
    private lateinit var optionAdapter: SettingsOptionAdapter
    private lateinit var categoryRecycler: RecyclerView
    private lateinit var optionsRecycler: RecyclerView
    private lateinit var contentContainer: FrameLayout
    private lateinit var languageAdapter: LanguageAdapter
    private lateinit var languageRecycler: RecyclerView

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

        SettingCategory(
            "Playlists",
            R.drawable.ic_playlist
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

        // Cleaning
        SettingCategory(
            "Cleaning",
            R.drawable.ic_cleaning
        )
    )

    override fun onViewCreated(
        view: View,
        savedInstanceState: Bundle?
    ) {
        super.onViewCreated(view, savedInstanceState)
        categoryRecycler = view.findViewById(R.id.categoryRecycler)
        optionsRecycler = view.findViewById(R.id.optionsRecycler)
        contentContainer = view.findViewById(R.id.contentContainer)
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
                    // Cleaning
                    "clean_recent" -> {
                        viewLifecycleOwner.lifecycleScope.launch {playbackHistoryRepository.clear()}
                    }

                    "clean_search" -> {
                        // TODO
                        // حذف Search History
                    }

                    "clean_favorites" -> {
                        // TODO
                        // حذف Favorites
                    }

                    "clean_playlists" -> {
                        // TODO
                        // حذف Playlists
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
                categoryRecycler.scrollToPosition(6)

            }
            loadCategory(6)
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

        layoutInflater.inflate(
            R.layout.view_profile_info,
            contentContainer,
            true
        )

        val hero =
            HeroWidget(
                contentContainer.findViewById(R.id.heroContainer),
                viewLifecycleOwner
            )

        val logout =
            contentContainer.findViewById<View>(R.id.logoutButton)

        logout.setOnClickListener {

            // TODO Logout

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
    private fun showGeneralSettings(){
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(
            listOf(
                SettingOption(
                    "general_exit",
                    "Exit App",
                    true
                ),
                SettingOption(
                    "general_keep",
                    "Keep Playing"
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
    private fun showCleaning() {
        optionsRecycler.visibility = View.VISIBLE
        optionAdapter.submit(

            listOf(

                SettingOption(
                    "clean_recent",
                    "Clear Recently Played"
                ),

                SettingOption(
                    "clean_search",
                    "Clear Search History"
                ),

                SettingOption(
                    "clean_favorites",
                    "Delete Favorites"
                ),

                SettingOption(
                    "clean_playlists",
                    "Delete Playlists"
                )

            )

        )
    }
    private fun hideAllContent() {
        optionsRecycler.visibility = View.GONE
        languageRecycler.visibility = View.GONE
        contentContainer.visibility = View.GONE
        contentContainer.removeAllViews()
    }
    private fun loadCategory(index: Int) {
        hideAllContent()
        when (index) {

            0 -> showProfile()
            1 -> showProfile()
            2 -> showAudioSettings()
            3 -> showVideoSettings()
            4 -> showGeneralSettings()
            5 -> showThemeSettings()
            6 -> showLanguageSettings()
            7 -> showCleaning()
        }
    }
}