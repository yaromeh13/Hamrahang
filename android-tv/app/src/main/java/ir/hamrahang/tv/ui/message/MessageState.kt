package ir.hamrahang.tv.ui.message

data class MessageAction(
    val label: String,
    val iconRes: Int,
    val onClick: () -> Unit
)

data class MessageState(
    val visible: Boolean = false,
    val text: String = "",
    val type: MessageType = MessageType.INFO,
    val actions: List<MessageAction> = emptyList()
)
