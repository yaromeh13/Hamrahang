package ir.hamrahang.tv.data

import ir.hamrahang.tv.domain.LocalizedTitle

data class ParentMini(
    val type: String,
    val id: String,
    override val name: String? = null,
    override val names: Map<String, String> = emptyMap(),
    val meta: MediaMeta? = null
) : LocalizedTitle
