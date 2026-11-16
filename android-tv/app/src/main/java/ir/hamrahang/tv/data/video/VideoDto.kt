package ir.hamrahang.tv.data.video

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.song.SongDto

data class VideoDto(
    val id: String?,
    val name: String?,
    val name_fa: String? = null,
    val names: Map<String, String>? = null,
    val url: String? = null,
    val artists: List<VideoArtistDto>? = null,
    val songs: List<SongDto>? = null,
    val meta: MediaMeta? = null,
    val sources: List<MediaSource>? = null
)
