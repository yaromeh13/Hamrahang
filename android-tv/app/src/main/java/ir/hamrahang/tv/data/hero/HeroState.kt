package ir.hamrahang.tv.hero

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.data.local.entity.PlaylistEntity

enum class HeroStyle {
    VIDEO,
    SONG,
    PODCAST,
    ARTIST,
    ALBUM,
    PLAYLIST
}

data class HeroState(
    val style: HeroStyle,
    val song: Song? = null,
    val podcast: Podcast? = null,
    val album: Album? = null,
    val artist: Artist? = null,
    val musicVideo: MusicVideo? = null,
    val playlist: PlaylistEntity? = null
)
