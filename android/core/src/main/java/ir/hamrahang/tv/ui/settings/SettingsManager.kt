package ir.hamrahang.tv.settings

import android.content.Context
import ir.hamrahang.tv.utils.AppState

object SettingsManager {

    private const val PREF_NAME = "app_settings"

    private const val KEY_LANGUAGE = "language"
    private const val KEY_AUDIO_QUALITY = "audio_quality"
    private const val KEY_VIDEO_QUALITY = "video_quality"
    private const val KEY_THEME = "theme"
    private const val KEY_EXIT_MODE = "exit_mode"
    private const val KEY_REPEAT_MODE = "repeat_mode"

    private lateinit var context: Context

    fun init(context: Context) {

        this.context = context.applicationContext

        load()

    }

    private val prefs
        get() = context.getSharedPreferences(
            PREF_NAME,
            Context.MODE_PRIVATE
        )

    private fun load() {

        AppState.language =
            prefs.getString(KEY_LANGUAGE, "fa") ?: "fa"

        AppState.audioQuality =
            QualityLevel.valueOf(
                prefs.getString(
                    KEY_AUDIO_QUALITY,
                    QualityLevel.MEDIUM.name
                )!!
            )

        AppState.videoQuality =
            QualityLevel.valueOf(
                prefs.getString(
                    KEY_VIDEO_QUALITY,
                    QualityLevel.MEDIUM.name
                )!!
            )

        AppState.theme =
            ThemeMode.valueOf(
                prefs.getString(
                    KEY_THEME,
                    ThemeMode.DARK.name
                )!!
            )

        AppState.exitMode =
            ExitMode.valueOf(
                prefs.getString(
                    KEY_EXIT_MODE,
                    ExitMode.KEEP_PLAYING.name
                )!!
            )

    }

    fun saveLanguage(value: String) {

        prefs.edit().putString(KEY_LANGUAGE, value).apply()

        AppState.language = value

    }

    fun saveAudioQuality(value: QualityLevel) {

        prefs.edit()
            .putString(KEY_AUDIO_QUALITY, value.name)
            .apply()

        AppState.audioQuality = value

    }

    fun saveVideoQuality(value: QualityLevel) {

        prefs.edit()
            .putString(KEY_VIDEO_QUALITY, value.name)
            .apply()

        AppState.videoQuality = value

    }

    fun saveTheme(value: ThemeMode) {

        prefs.edit()
            .putString(KEY_THEME, value.name)
            .apply()

        AppState.theme = value

    }

    fun saveExitMode(value: ExitMode) {

        prefs.edit()
            .putString(KEY_EXIT_MODE, value.name)
            .apply()

        AppState.exitMode = value

    }

    fun getRepeatMode(): String =
        prefs.getString(KEY_REPEAT_MODE, "off") ?: "off"

    fun saveRepeatMode(value: String) {
        val safeValue = if (value == "one" || value == "all") value else "off"
        prefs.edit().putString(KEY_REPEAT_MODE, safeValue).apply()
    }

}