package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.musicvideo.VideoDto
import ir.hamrahang.tv.data.song.SongDto

data class ArtistDetailsDto(

    val id: String?,
    val name: String?,
    val name_fa: String?,
    val thumb: String?,
    val bd: String?,
    val ins: String?,
    val tlg: String?,
    val albums: List<AlbumDto>?,
    val songs: List<SongDto>?,
    val videos: List<VideoDto>?
)