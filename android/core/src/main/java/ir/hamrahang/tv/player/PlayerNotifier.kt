package ir.hamrahang.tv.player

/** User-facing player messages; implemented by each app. */
interface PlayerNotifier {
    fun info(text: String)
    fun warning(text: String)
    fun error(text: String)
}
