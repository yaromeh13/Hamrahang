package ir.hamrahang.tv.data.song

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.video.VideoArtistDto

data class SongDto(
    val id: String?,
    val name: String?,
    val names: Map<String, String>? = null,
    val artists: List<VideoArtistDto>? = null,
    val parents: List<ParentMini>? = null,
    val meta: MediaMeta? = null,
    val sources: List<MediaSource>? = null,
    val trk: Int? = null
)
