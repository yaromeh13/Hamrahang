package ir.hamrahang.tv.data.playlist

import com.google.gson.annotations.SerializedName

data class PlaylistMetaDto(
    val nci: Int? = null,
    val tmb: String? = null,
    val des: String? = null
)

data class PlaylistOwnerDto(
    val id: Long? = null,
    val name: String? = null
)

data class PlaylistDto(
    val id: Long? = null,
    val name: String? = null,
    val name_fa: String? = null,
    val url: String? = null,
    val owner: PlaylistOwnerDto? = null,
    @SerializedName("public") val isPublic: Boolean? = null,
    val meta: PlaylistMetaDto? = null,
    val songs: List<PlaylistContentDto>? = null,
    val videos: List<PlaylistContentDto>? = null
)

data class PlaylistContentMetaDto(
    val type: String? = null
)

data class PlaylistContentDto(
    val id: Long? = null,
    val name: String? = null,
    val position: Int? = null,
    val meta: PlaylistContentMetaDto? = null
)

data class PlaylistEnvelope(
    val playlist: PlaylistDto? = null
)

data class PlaylistCreatedDto(
    val local_id: String? = null,
    val playlist: PlaylistEnvelope? = null,
    val capped: Boolean? = null
)

data class PlaylistSyncItem(
    val type: String,
    val id: Long
)

data class PlaylistSyncInput(
    val local_id: String,
    val name: String,
    val items: List<PlaylistSyncItem>
)

data class PlaylistApiResponse(
    val ok: Boolean? = null,
    val error: String? = null,
    val playlists: List<PlaylistDto>? = null,
    val playlist: PlaylistEnvelope? = null,
    val created: List<PlaylistCreatedDto>? = null,
    val skipped: List<Map<String, Any?>>? = null
)
