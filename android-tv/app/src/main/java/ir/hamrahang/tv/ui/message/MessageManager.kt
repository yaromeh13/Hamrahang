package ir.hamrahang.tv.ui.message

import kotlinx.coroutines.*

import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow

object MessageManager {

    private val scope =
        CoroutineScope(Dispatchers.Main)

    private var hideJob: Job? = null

    private val _state =
        MutableStateFlow(MessageState())

    val state =
        _state.asStateFlow()

    fun info(

        text: String,

        duration: Long = 2500

    ) {

        show(
            text,
            MessageType.INFO,
            duration
        )

    }

    fun success(

        text: String,

        duration: Long = 2500

    ) {

        show(
            text,
            MessageType.SUCCESS,
            duration
        )

    }

    fun warning(

        text: String,

        duration: Long = 2500

    ) {

        show(
            text,
            MessageType.WARNING,
            duration
        )

    }

    fun error(

        text: String,

        duration: Long = 3000

    ) {

        show(
            text,
            MessageType.ERROR,
            duration
        )

    }

    private fun show(

        text: String,

        type: MessageType,

        duration: Long

    ) {

        hideJob?.cancel()

        _state.value =

            MessageState(

                visible = true,

                text = text,

                type = type

            )

        hideJob = scope.launch {

            delay(duration)

            hide()

        }

    }

    fun hide() {

        _state.value = MessageState()

    }

}