package ir.hamrahang.tv.network

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.asSharedFlow

object NetworkEvents {

    sealed class Event {
        object Timeout : Event()
        object InternetAvailable : Event()
        object InternetLost : Event()
        object ApiAvailable : Event()
        object ApiUnavailable : Event()
    }

    private val _events = MutableSharedFlow<Event>(
        replay = 0,
        extraBufferCapacity = 16
    )

    val events: SharedFlow<Event>
        get() = _events.asSharedFlow()

    @Volatile
    private var lastApiState: Boolean? = null

    fun reportTimeout() {
        _events.tryEmit(Event.Timeout)
    }

    fun reportInternetAvailable() {
        _events.tryEmit(Event.InternetAvailable)
    }

    fun reportInternetLost() {
        _events.tryEmit(Event.InternetLost)
    }

    /** Called only after a real HTTP response from hamrahang.ir. */
    fun reportApiAvailable() {
        if (lastApiState == true) return
        lastApiState = true
        _events.tryEmit(Event.ApiAvailable)
    }

    /** A failed API transport marks reachability unknown/unavailable until a real response. */
    fun reportApiUnavailable() {
        if (lastApiState == false) return
        lastApiState = false
        _events.tryEmit(Event.ApiUnavailable)
    }
}
