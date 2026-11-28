package ir.hamrahang.tv.data

/**
 * One lyrics variant returned by the Hamrahang web API.
 * The body may contain timestamped LRC or plain text, matching web behavior.
 */
data class LyricsEntry(
    val lng: String? = null,
    val knd: String? = null,
    val fmt: String? = null,
    val src: String? = null,
    val body: String? = null
)
