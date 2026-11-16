package ir.hamrahang.tv.hero

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.musicvideo.MusicVideo

sealed class HeroEvent {

    data class VideoFocused(val video: MusicVideo) : HeroEvent()
    data class SongFocused(val song: Song) : HeroEvent()
    data class ArtistFocused(val artist: Artist) : HeroEvent()
    data class AlbumFocused(val album: Album) : HeroEvent()
}