package ir.hamrahang.tv.data.artist

import com.google.gson.JsonElement
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.video.VideoDto

data class ArtistDto(
    // ---------- Basic ----------
    val id: String,
    val name: String? = null,
    // The current web API returns alternate localized names in "names".
    val name_fa: String? = null,
    val url: String? = null,
    val names: JsonElement? = null,

    // ---------- Meta ----------
    val meta: ArtistMeta? = null,

    // ---------- Relations ----------
    val albums: List<AlbumDto>? = null,
    val songs: List<SongDto>? = null,
    val musicvideos: List<VideoDto>? = null,
    val videos: List<VideoDto>? = null,
    val podcasts: List<SongDto>? = null,

    val full: Boolean? = null
)
