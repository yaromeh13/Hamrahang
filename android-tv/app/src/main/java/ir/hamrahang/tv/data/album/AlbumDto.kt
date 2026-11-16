package ir.hamrahang.tv.data.album

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.song.SongDto

data class AlbumDto(
    val id: String,
    val name: String?,
    val name_fa: String?,
    //val url: String?,
    val artists: List<ArtistDto>? = null,
    val songs: List<SongDto>? = null,
    val meta: MediaMeta? = null,
)
