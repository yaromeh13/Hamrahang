package ir.hamrahang.tv.data.search

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.podcast.PodcastDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.video.VideoDto

data class SearchResponseDto(
    val search: SearchDto
)

data class SearchDto(
    val q: String? = null,
    val artists: List<ArtistDto>? = null,
    val albums: List<AlbumDto>? = null,
    val songs: List<SongDto>? = null,
    val musicvideos: List<VideoDto>? = null,
    val podcasts: List<PodcastDto>? = null,
    val videos: List<VideoDto>? = null
)