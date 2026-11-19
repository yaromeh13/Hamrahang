package ir.hamrahang.tv.lyrics

import android.content.Context

class LyricsSettings(context: Context) {
    private val prefs = context.applicationContext
        .getSharedPreferences("lyrics_settings", Context.MODE_PRIVATE)

    fun read(): LyricsPreferences = LyricsPreferences(
        enabled = prefs.getBoolean("enabled", true),
        language = prefs.getString("language", "fa") ?: "fa",
        fontSize = prefs.getInt("font_size", 16).coerceIn(12, 32),
        fontFamily = prefs.getString("font_family", "") ?: "",
        viewMode = runCatching {
            LyricsViewMode.valueOf(prefs.getString("view_mode", "FOCUS") ?: "FOCUS")
        }.getOrDefault(LyricsViewMode.FOCUS)
    )

    fun update(value: LyricsPreferences) {
        prefs.edit()
            .putBoolean("enabled", value.enabled)
            .putString("language", value.language)
            .putInt("font_size", value.fontSize.coerceIn(12, 32))
            .putString("font_family", value.fontFamily)
            .putString("view_mode", value.viewMode.name)
            .apply()
    }
}
