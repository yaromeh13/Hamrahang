package ir.hamrahang.tv.network

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.asSharedFlow

object NetworkEvents {

    sealed class Event {

        object Timeout : Event()

        object InternetAvailable : Event()

        object InternetLost : Event()

    }

    private val _events =
        MutableSharedFlow<Event>(
            replay = 0,
            extraBufferCapacity = 8
        )

    val events: SharedFlow<Event>
        get() = _events.asSharedFlow()

    //--------------------------------------------------
    // Timeout
    //--------------------------------------------------

    fun reportTimeout() {

        _events.tryEmit(
            Event.Timeout
        )

    }

    //--------------------------------------------------
    // Internet
    //--------------------------------------------------

    fun reportInternetAvailable() {

        _events.tryEmit(
            Event.InternetAvailable
        )

    }

    fun reportInternetLost() {

        _events.tryEmit(
            Event.InternetLost
        )

    }

}
/*

package ir.hamrahang.tv.network

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow

object NetworkEvents {




    private val _events = MutableSharedFlow<Unit>()

    val events: SharedFlow<Unit>
        get() = _events

    fun reportTimeout() {

        _events.tryEmit(Unit)

    }
}*/