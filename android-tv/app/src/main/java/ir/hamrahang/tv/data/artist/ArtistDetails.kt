package ir.hamrahang.tv.domain.artist

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.musicvideo.MusicVideo

data class ArtistDetails(

    val id: String,
    val name: String,
    val image: String,
    val birthDate: String?,
    val instagram: String?,
    val telegram: String?,
    val albums: List<Album>,
    val songs: List<Song>,
    val videos: List<MusicVideo>
)