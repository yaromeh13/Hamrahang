package ir.hamrahang.tv.data.video

import com.google.gson.JsonElement
import com.google.gson.annotations.JsonAdapter
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta

@JsonAdapter(VideoArtistDtoAdapter::class)
data class VideoArtistDto(
    val id: String,
    val name: String? = null,
    val name_fa: String? = null,
    val names: JsonElement? = null,
    val meta: ArtistMeta? = null
) {
    fun toArtistDto(): ArtistDto =
        ArtistDto(
            id = id,
            name = name,
            name_fa = name_fa,
            meta = meta
        )
}
