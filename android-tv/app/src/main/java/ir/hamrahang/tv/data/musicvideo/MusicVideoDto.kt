package ir.hamrahang.tv.data.musicvideo

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.song.SongDto

data class MusicVideoDto(
    val id: String,
    val name: String?,
    val name_fa: String?,
    //val url: String?,
    val artists: List<ArtistDto>? = null,
    val songs: List<SongDto>? = null,
    val meta: MediaMeta? = null,
    val sources: List<MediaSource>? = null,
)