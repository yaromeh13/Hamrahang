package ir.hamrahang.tv.domain.musicvideo

import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SongMini
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.ImageResolver

data class MusicVideo(

    val id: String,
    override val title: String? = null,
    override val titleFa: String? = null,
    val artists: List<Artist> = emptyList(),
    val songs: List<String> = emptyList(),
    val meta: MediaMeta? = null,
    val sources: List<MediaSource> = emptyList()

): LocalizedTitle{
    val thumb:String
        get() = ImageResolver.thumb(meta?.tmb)
}