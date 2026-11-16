package ir.hamrahang.tv.utils

import ir.hamrahang.tv.settings.ExitMode
import ir.hamrahang.tv.settings.QualityLevel
import ir.hamrahang.tv.settings.ThemeMode

object AppState {

    lateinit var audioQuality: QualityLevel
    lateinit var videoQuality: QualityLevel
    lateinit var theme: ThemeMode
    lateinit var exitMode: ExitMode
    @Volatile
    var language: String = "fa"
       // private set

    var restoreSettingsLanguage = false
    var offlineMode = false
    fun isPersian() = language == "fa"

}