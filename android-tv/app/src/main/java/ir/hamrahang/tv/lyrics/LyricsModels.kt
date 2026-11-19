package ir.hamrahang.tv.lyrics

data class LyricsLine(
    val timeMs: Long?,
    val text: String,
    val isIntro: Boolean = false
)

data class ParsedLyrics(
    val lines: List<LyricsLine>,
    val offsetMs: Long = 0L
) {
    val hasTimedLines: Boolean get() = lines.any { it.timeMs != null }
}

enum class LyricsViewMode {
    NORMAL, FOCUS, LINE, CARD
}

data class LyricsPreferences(
    val enabled: Boolean = true,
    val language: String = "fa",
    val fontSize: Int = 16,
    val fontFamily: String = "",
    val viewMode: LyricsViewMode = LyricsViewMode.FOCUS
)

data class LyricsFrame(
    val lines: List<LyricsLine>,
    val activeIndex: Int,
    val viewMode: LyricsViewMode
)
