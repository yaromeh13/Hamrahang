package ir.hamrahang.tv.data.playlist

import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.domain.musicvideo.MusicVideo

sealed class PlaylistMedia {
    data class SongItem(val value: Song) : PlaylistMedia()
    data class PodcastItem(val value: Podcast) : PlaylistMedia()
    data class VideoItem(val value: Video) : PlaylistMedia()
    data class MusicVideoItem(val value: MusicVideo) : PlaylistMedia()
}
