package ir.hamrahang.tv.lyrics

import ir.hamrahang.tv.data.LyricsEntry

object LyricsParser {
    private val timestampRegex =
        Regex("""\[(\d{1,3}):(\d{2})(?:[.:](\d{1,3}))?]""")
    private val offsetRegex = Regex("""\[offset:([+-]?\d+)]""", RegexOption.IGNORE_CASE)

    fun parse(source: String?): ParsedLyrics {
        if (source.isNullOrBlank()) return ParsedLyrics(emptyList())

        val offset = offsetRegex.find(source)?.groupValues?.getOrNull(1)?.toLongOrNull() ?: 0L
        val result = mutableListOf<LyricsLine>()
        var intro = false

        source.lineSequence().forEach { raw ->
            val line = raw.trim()
            if (line.equals("[intro]", true)) {
                intro = true
                return@forEach
            }
            if (line.equals("[/intro]", true)) {
                intro = false
                return@forEach
            }
            if (line.isBlank() || offsetRegex.containsMatchIn(line)) return@forEach

            val stamps = timestampRegex.findAll(line).toList()
            val text = timestampRegex.replace(line, "").trim()
            if (stamps.isEmpty()) {
                result += LyricsLine(null, line, intro)
            } else if (text.isNotEmpty()) {
                stamps.forEach { match ->
                    val minutes = match.groupValues[1].toLongOrNull() ?: 0L
                    val seconds = match.groupValues[2].toLongOrNull() ?: 0L
                    val fraction = match.groupValues.getOrNull(3).orEmpty()
                    val millis = when (fraction.length) {
                        1 -> (fraction.toLongOrNull() ?: 0L) * 100L
                        2 -> (fraction.toLongOrNull() ?: 0L) * 10L
                        3 -> fraction.toLongOrNull() ?: 0L
                        else -> 0L
                    }
                    result += LyricsLine(
                        (minutes * 60_000L) + (seconds * 1_000L) + millis,
                        text,
                        intro
                    )
                }
            }
        }
        return ParsedLyrics(result, offset)
    }

    fun parseEntry(entry: LyricsEntry?): ParsedLyrics = parse(entry?.body)

    fun selectEntry(entries: List<LyricsEntry>, language: String): LyricsEntry? {
        val usable = entries.filter { !it.body.isNullOrBlank() }
        return usable.firstOrNull { it.lng?.equals(language, true) == true }
            ?: usable.firstOrNull {
                it.lng?.substringBefore("-")?.equals(language.substringBefore("-"), true) == true
            }
            ?: usable.firstOrNull()
    }

    fun isLrc(source: String?): Boolean =
        !source.isNullOrBlank() && timestampRegex.containsMatchIn(source)
}
