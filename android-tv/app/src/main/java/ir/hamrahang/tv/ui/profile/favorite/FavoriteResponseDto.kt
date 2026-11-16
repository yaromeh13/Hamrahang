package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.musicvideo.MusicVideoDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.video.VideoDto

data class FavoriteResponseDto(

    val songs: List<SongDto> = emptyList(),

    val videos: List<VideoDto> = emptyList(),

    val artists: List<ArtistDto> = emptyList(),

    val albums: List<AlbumDto> = emptyList()

)