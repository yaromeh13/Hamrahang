package ir.hamrahang.tv.data.album

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.video.VideoArtistDto
import ir.hamrahang.tv.data.song.SongDto

data class AlbumDto(
    val id: String,
    val name: String? = null,
    // The current web API does not return name_fa; localized names are in "names".
    val name_fa: String? = null,
    val url: String? = null,
    val names: Map<String, String>? = null,
    val artists: List<VideoArtistDto>? = null,
    val songs: List<SongDto>? = null,
    val meta: MediaMeta? = null,
    val full: Boolean? = null
)
