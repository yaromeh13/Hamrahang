package ir.hamrahang.tv.hero

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.song.Song

enum class HeroStyle {
    VIDEO,
    SONG,
    ARTIST,
    ALBUM
}

data class HeroState(
    val style: HeroStyle,
    val song: Song? = null,
    val album: Album? = null,
    val artist: Artist? = null,
    val musicVideo: MusicVideo? = null,
)
