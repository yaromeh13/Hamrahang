package ir.hamrahang.tv.domain.podcast

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.artist.ArtistDto

data class Podcast(

    val id: String,
    val title: String,
    val titleFa: String? = null,
    val url: String? = null,
    val artists: List<ArtistDto> = emptyList(),
    val parents: List<ParentMini> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList()

)