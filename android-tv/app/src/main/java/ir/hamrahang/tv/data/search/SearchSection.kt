package ir.hamrahang.tv.data.search

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video

sealed class SearchSection {

    data class Artists(val items: List<Artist>) : SearchSection()
    data class Songs(val items: List<Song>) : SearchSection()
    data class Albums(val items: List<Album>) : SearchSection()
    data class MusicVideos(val items: List<MusicVideo>) : SearchSection()
    data class Videos(val items: List<Video>) : SearchSection()
    data class Podcasts(val items: List<Podcast>) : SearchSection()
}
