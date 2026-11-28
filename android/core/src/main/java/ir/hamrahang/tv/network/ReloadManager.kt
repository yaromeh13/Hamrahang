package ir.hamrahang.tv.network

object ReloadManager {

    private var reloadAction: (() -> Unit)? = null

    fun register(action: () -> Unit) {
        reloadAction = action
    }

    fun unregister() {
        reloadAction = null
    }

    fun reload() {
        reloadAction?.invoke()
    }
}