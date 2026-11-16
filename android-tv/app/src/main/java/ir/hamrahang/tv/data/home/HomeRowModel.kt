package ir.hamrahang.tv.domain.home

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.song.Song

data class HomeRowModel(
    val type: HomeRowType,
    val title: Int? = null,
    val videos: List<MusicVideo>,
    val artists: List<Artist> = emptyList(),
    val songs: List<Song> = emptyList(),
    val albums: List<Album> = emptyList(),
    val isLoading: Boolean = true
)