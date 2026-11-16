package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SongMini
import ir.hamrahang.tv.data.artist.ArtistDto

data class Video(

    val id: String,
    val title: String,
    val artists: List<ArtistDto> = emptyList(),
    val songs: List<SongMini> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList()
)