package ir.hamrahang.tv.ui

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.playback.PlaybackSession

sealed class UiClickEvent {

    data class VideoClick(
        val video: Video
    ) : UiClickEvent()

    data class MusicVideoClick(
        val video: MusicVideo
    ) : UiClickEvent()

    data class SongClick(
        val song: Song
    ) : UiClickEvent()

    data class PodcastClick(
        val podcast: Podcast
    ) : UiClickEvent()

    data class ArtistClick(
        val artist: Artist
    ) : UiClickEvent()

    data class AlbumClick(
        val album: Album
    ) : UiClickEvent()

    data class PlaySession(
        val session: PlaybackSession
    ) : UiClickEvent()
}