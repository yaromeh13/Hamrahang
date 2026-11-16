package ir.hamrahang.tv.ui.message

data class MessageState(

    val visible: Boolean = false,

    val text: String = "",

    val type: MessageType = MessageType.INFO

)