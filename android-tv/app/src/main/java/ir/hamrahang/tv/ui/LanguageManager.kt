package ir.hamrahang.tv.utils

import android.content.Context
import android.content.SharedPreferences
import android.content.res.Configuration
import java.util.Locale

object LanguageManager {

    private const val PREF_NAME = "app_settings"
    private const val KEY_LANGUAGE = "language"

    fun setLanguage(context: Context, lang: String) {

        val prefs = context.getSharedPreferences(
            PREF_NAME,
            Context.MODE_PRIVATE
        )

        prefs.edit()
            .putString(KEY_LANGUAGE, lang)
            .apply()

        AppState.setLanguage(lang)
    }

    fun getLanguage(context: Context): String {

        val prefs = context.getSharedPreferences(
            PREF_NAME,
            Context.MODE_PRIVATE
        )

        val saved = prefs.getString(KEY_LANGUAGE, null)

        if (saved != null) {
            AppState.setLanguage(saved) // sync state
            return saved
        }

        val systemLang = Locale.getDefault().language

        return if (systemLang == "fa")
            "fa"
        else
            "en"
    }

    fun isPersian(context: Context): Boolean {
        return getLanguage(context) == "fa"
    }

    fun applyLanguage(context: Context): Context {

        val lang = getLanguage(context)

        val locale = Locale(lang)
        Locale.setDefault(locale)

        val config = Configuration(
            context.resources.configuration
        )

        config.setLocale(locale)
        config.setLayoutDirection(locale)

        context.resources.updateConfiguration(
            config,
            context.resources.displayMetrics
        )

        return context.createConfigurationContext(config)
    }

}