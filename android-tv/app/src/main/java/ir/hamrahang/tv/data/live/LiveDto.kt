package ir.hamrahang.tv.data.live

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource

data class LiveDto(
    val id: String? = null,
    val name: String? = null,
    val names: Map<String, String>? = null,
    val meta: MediaMeta? = null,
    val sources: List<MediaSource>? = null
)
