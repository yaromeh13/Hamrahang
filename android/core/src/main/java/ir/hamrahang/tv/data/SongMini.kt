package ir.hamrahang.tv.data

import ir.hamrahang.tv.domain.LocalizedTitle

data class SongMini(
    val id: String? = null,
    override val name: String? = null,
    override val names: Map<String, String>? = null,
    val thumb: String? = null
) : LocalizedTitle
