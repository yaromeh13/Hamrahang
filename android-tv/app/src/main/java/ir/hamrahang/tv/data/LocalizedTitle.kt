package ir.hamrahang.tv.domain

import ir.hamrahang.tv.utils.AppState
import java.util.Locale

interface LocalizedTitle {
    val name: String?
    val names: Map<String, String>

    val title: String
        get() {
            val language = AppState.language.trim().lowercase(Locale.ROOT)
            val baseLanguage = language.substringBefore('-').substringBefore('_')
            val localized = names[language]?.trim()?.takeIf { it.isNotEmpty() }
                ?: names[baseLanguage]?.trim()?.takeIf { it.isNotEmpty() }

            return localized
                ?: name?.trim()?.takeIf { it.isNotEmpty() }
                ?: ""
        }
}
