package ir.hamrahang.tv.data.video

import com.google.gson.annotations.JsonAdapter
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta

@JsonAdapter(VideoArtistDtoAdapter::class)
data class VideoArtistDto(
    val id: String,
    val name: String? = null,
    val names: Map<String, String>? = null,
    val meta: ArtistMeta? = null
) {
    fun toArtistDto(): ArtistDto =
        ArtistDto(
            id = id,
            name = name,
            names = names,
            meta = meta
        )
}
