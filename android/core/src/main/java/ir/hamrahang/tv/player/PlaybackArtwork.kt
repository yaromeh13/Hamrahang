package ir.hamrahang.tv.player

/** Artwork of the current media; each app decides how to show it. */
object PlaybackArtwork {
    var listener: ((Any?) -> Unit)? = null

    fun show(source: Any?) {
        listener?.invoke(source)
    }
}
