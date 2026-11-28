package ir.hamrahang.app.ui

import android.os.Bundle
import android.view.View
import androidx.appcompat.app.AppCompatDelegate
import androidx.core.os.LocaleListCompat
import androidx.fragment.app.Fragment
import ir.hamrahang.app.R
import ir.hamrahang.tv.settings.SettingsManager

class SettingsFragment : Fragment(R.layout.fragment_settings) {

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        view.findViewById<View>(R.id.languageFa).setOnClickListener { setLanguage("fa") }
        view.findViewById<View>(R.id.languageEn).setOnClickListener { setLanguage("en") }
    }

    private fun setLanguage(language: String) {
        // Saved first: content titles read AppState.language.
        SettingsManager.saveLanguage(language)
        AppCompatDelegate.setApplicationLocales(LocaleListCompat.forLanguageTags(language))
    }
}
