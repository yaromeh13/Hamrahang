package ir.hamrahang.tv.data.favorite

/**
 * Matches the web gja() envelope: POST /?_qjs=uac with { "q": { ... } }.
 */
data class UacRequest(
    val q: UacAction
)

data class UacAction(
    val a: String,
    val p: String? = null,
    val q: List<String>? = null,
    val items: Map<String, List<String>>? = null
)

data class UacResponse(
    val ok: Boolean? = null,
    val error: String? = null,
    val favorites: Map<String, List<String>>? = null,
    val type: String? = null,
    val id: String? = null,
    val favorited: Boolean? = null
)
