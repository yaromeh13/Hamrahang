package ir.hamrahang.tv.data

data class ParentMini(
    val type: String,
    val id: String,
    val name: String? = null,
    val name_fa: String? = null,
    val meta: MediaMeta? = null
)