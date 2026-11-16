package ir.hamrahang.tv.data.song

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.artist.ArtistDto

data class SongDto(
    val id: String?,
    val name: String?,
    val name_fa: String?,
    val artists: List<ArtistDto>? = null,
    val parents: List<ParentMini>? = null,
    val meta: MediaMeta? = null,
    val sources: List<MediaSource>? = null,
    // Album detail API returns the track position as "trk".
    val trk: Int? = null
)
