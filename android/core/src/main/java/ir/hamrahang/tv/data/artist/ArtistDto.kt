package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.video.VideoDto

data class ArtistDto(
    val id: String,
    val name: String? = null,
    val url: String? = null,
    val names: Map<String, String>? = null,
    val meta: ArtistMeta? = null,
    val albums: List<AlbumDto>? = null,
    val songs: List<SongDto>? = null,
    val musicvideos: List<VideoDto>? = null,
    val videos: List<VideoDto>? = null,
    val podcasts: List<SongDto>? = null,
    val full: Boolean? = null
)
