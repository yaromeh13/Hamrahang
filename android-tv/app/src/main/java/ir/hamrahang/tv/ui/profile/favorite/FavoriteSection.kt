package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video

sealed class FavoriteSection {

    data class Songs(

        val items: List<Song>

    ) : FavoriteSection()

    data class MusicVideos(

        val items: List<MusicVideo>

    ) : FavoriteSection()

    data class Videos(

        val items: List<Video>

    ) : FavoriteSection()

    data class Artists(

        val items: List<Artist>

    ) : FavoriteSection()

    data class Albums(

        val items: List<Album>

    ) : FavoriteSection()

}