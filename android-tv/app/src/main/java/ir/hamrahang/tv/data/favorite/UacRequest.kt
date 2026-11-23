package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.data.auth.HistorySyncItem
import ir.hamrahang.tv.data.playlist.PlaylistCreatedDto
import ir.hamrahang.tv.data.playlist.PlaylistDto
import ir.hamrahang.tv.data.playlist.PlaylistEnvelope
import ir.hamrahang.tv.data.playlist.PlaylistSyncInput

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
    val items: Any? = null,
    val name: String? = null,
    val playlist_id: Long? = null,
    val type: String? = null,
    val id: Long? = null,
    val playlists: List<PlaylistSyncInput>? = null
)

data class UacResponse(
    val ok: Boolean? = null,
    val error: String? = null,
    val favorites: Map<String, List<String>>? = null,
    val history: List<HistorySyncItem>? = null,
    val type: String? = null,
    val id: String? = null,
    val favorited: Boolean? = null,
    val playlist: PlaylistEnvelope? = null,
    val playlists: List<PlaylistDto>? = null,
    val created: List<PlaylistCreatedDto>? = null,
    val skipped: List<Map<String, Any?>>? = null
)
