package ir.hamrahang.tv.ui.message

import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow

object MessageManager {
    private val scope = CoroutineScope(Dispatchers.Main)
    private var hideJob: Job? = null

    private val _state = MutableStateFlow(MessageState())
    val state = _state.asStateFlow()

    fun info(text: String, duration: Long = 2500) =
        show(text, MessageType.INFO, duration)

    fun success(text: String, duration: Long = 2500) =
        show(text, MessageType.SUCCESS, duration)

    fun warning(text: String, duration: Long = 2500) =
        show(text, MessageType.WARNING, duration)

    fun error(text: String, duration: Long = 3000) =
        show(text, MessageType.ERROR, duration)

    /**
     * Displays a persistent confirmation in the same app-wide message panel
     * used by MoreOptionsPanel and ordinary status messages.
     */
    fun confirm(
        text: String,
        positiveText: String,
        negativeText: String,
        onPositive: () -> Unit,
        onNegative: () -> Unit
    ) {
        hideJob?.cancel()
        _state.value = MessageState(
            visible = true,
            text = text,
            type = MessageType.INFO,
            actions = listOf(
                MessageAction(positiveText, ir.hamrahang.tv.R.drawable.ic_check, onPositive),
                MessageAction(negativeText, ir.hamrahang.tv.R.drawable.ic_error, onNegative)
            )
        )
    }

    private fun show(text: String, type: MessageType, duration: Long) {
        hideJob?.cancel()
        _state.value = MessageState(
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
        hideJob?.cancel()
        hideJob = null
        _state.value = MessageState()
    }

    fun performAction(action: MessageAction) {
        hide()
        action.onClick()
    }
}
