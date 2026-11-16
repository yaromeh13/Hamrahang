package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.podcast.PodcastDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.video.VideoDto

data class ArtistDto(

    // ---------- Basic ----------
    val id: String,
    val name: String?,
    val name_fa: String?,
    //val url: String?,

    // ---------- Meta ----------
    val meta: ArtistMeta? = null,

    // ---------- Relations (optional) ----------
    val albums: List<AlbumDto>? = null,
    val songs: List<SongDto>? = null,
    val musicvideos: List<VideoDto>? = null,
    val videos: List<VideoDto>? = null,
    val podcasts: List<SongDto>? = null,

)
