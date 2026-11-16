package ir.hamrahang.tv.data.podcast

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.video.VideoArtistDto

data class PodcastDto(

    val id: String?,
    val name: String?,
    val name_fa: String?,
    val url: String?,
    val artists: List<VideoArtistDto>?,
    val parents: List<ParentMini>?,
    val meta: MediaMeta?,
    val sources: List<MediaSource>?
)
