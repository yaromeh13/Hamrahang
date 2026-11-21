package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.data.auth.HistorySyncItem


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
    val items: Any? = null
)

data class UacResponse(
    val ok: Boolean? = null,
    val error: String? = null,
    val favorites: Map<String, List<String>>? = null,
    val history: List<HistorySyncItem>? = null,
    val type: String? = null,
    val id: String? = null,
    val favorited: Boolean? = null
)
