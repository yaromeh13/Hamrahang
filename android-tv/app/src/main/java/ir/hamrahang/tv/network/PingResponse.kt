package ir.hamrahang.tv.network

data class PingResponse(
    val status: String,
    val serverTime: Long,
    val version: String,
    val minVersion: Int,
    val forceUpdate: Boolean,
    val maintenance: Boolean
)