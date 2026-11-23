package ir.hamrahang.tv.domain.hero

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.data.local.entity.PlaylistEntity

sealed class HeroContent {

    data class VideoHero(
        val video: Video
    ) : HeroContent()

    data class MusicVideoHero(
        val video: MusicVideo
    ) : HeroContent()

    data class SongHero(
        val song: Song
    ) : HeroContent()

    data class PodcastHero(
        val podcast: Podcast
    ) : HeroContent()

    data class ArtistHero(
        val artist: Artist
    ) : HeroContent()

    data class AlbumHero(
        val album: Album
    ) : HeroContent()

    data class PlaylistHero(
        val playlist: PlaylistEntity
    ) : HeroContent()
}