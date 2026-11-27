package ir.hamrahang.tv.data.live

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SourceOwner
import com.google.gson.annotations.SerializedName

data class LiveGenre(
    val id: Long? = null,
    val slug: String? = null,
    val name: String? = null
)

data class ResolveLivesRequest(
    val ids: List<Long>
)

data class LiveDto(
    val id: String? = null,
    val name: String? = null,
    val url: String? = null,
    val names: Map<String, String>? = null,
    val meta: MediaMeta? = null,
    val genres: List<LiveGenre>? = null,
    val languages: List<String>? = null,
    val owner: SourceOwner? = null,
    val sources: List<MediaSource>? = null,
    @SerializedName("media_type") val mediaType: String? = null
) {
    // LIVE_CARD is a complete playable card: metadata plus its stream sources.
    val isFullCard: Boolean
        get() = !name.isNullOrBlank() && meta != null && sources != null
}
