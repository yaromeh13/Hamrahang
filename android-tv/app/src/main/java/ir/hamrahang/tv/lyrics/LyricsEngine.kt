package ir.hamrahang.tv.lyrics

enum class LyricsScope {
    PLAYER, MINI, CARD
}

/**
 * Stateful lyrics engine shared by the native player and compact/detail surfaces.
 * Playback time is always supplied by the active player; this class never owns a timer.
 */
class LyricsEngine {
    private var parsed = ParsedLyrics(emptyList())

    fun load(source: String?) {
        parsed = LyricsParser.parse(source)
    }

    fun current(): ParsedLyrics = parsed

    fun frame(
        positionMs: Long,
        preferences: LyricsPreferences,
        scope: LyricsScope = LyricsScope.PLAYER,
        isVideo: Boolean = false
    ): LyricsFrame {
        val mode = if (isVideo) LyricsViewMode.LINE else when (scope) {
            LyricsScope.PLAYER -> preferences.viewMode
            LyricsScope.MINI -> LyricsViewMode.FOCUS
            LyricsScope.CARD -> LyricsViewMode.NORMAL
        }
        return LyricsSynchronizer.frame(
            parsed = parsed,
            positionMs = positionMs,
            viewMode = mode,
            playerScope = scope == LyricsScope.PLAYER
        )
    }

    companion object {
        fun defaultPreferences(scope: LyricsScope): LyricsPreferences =
            when (scope) {
                LyricsScope.PLAYER -> LyricsPreferences()
                LyricsScope.MINI -> LyricsPreferences(
                    enabled = true,
                    language = "fa",
                    fontSize = 16,
                    viewMode = LyricsViewMode.FOCUS
                )
                LyricsScope.CARD -> LyricsPreferences(
                    enabled = true,
                    language = "fa",
                    fontSize = 20,
                    viewMode = LyricsViewMode.NORMAL
                )
            }
    }
}
