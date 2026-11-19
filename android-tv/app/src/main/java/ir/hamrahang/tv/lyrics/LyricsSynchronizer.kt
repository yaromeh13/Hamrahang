package ir.hamrahang.tv.lyrics

object LyricsSynchronizer {
    fun frame(
        parsed: ParsedLyrics,
        positionMs: Long,
        viewMode: LyricsViewMode,
        playerScope: Boolean = true
    ): LyricsFrame {
        val visible = if (playerScope) parsed.lines else parsed.lines.filterNot { it.isIntro }
        val activeTime = (positionMs - parsed.offsetMs).coerceAtLeast(0L)
        val activeIndex = visible.indexOfLast {
            it.timeMs != null && it.timeMs <= activeTime
        }
        return LyricsFrame(visible, activeIndex, viewMode)
    }
}
