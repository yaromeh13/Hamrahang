package ir.hamrahang.tv.data.video

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SongMini
import ir.hamrahang.tv.data.artist.ArtistDto

data class VideoDto(
    val id: String?,
    val name: String?,
    val name_fa: String?,
    val url: String?,
    val artists: List<ArtistDto>?,
    val songs: List<SongMini>?,
    val meta: MediaMeta?,
    val sources: List<MediaSource>?
)

