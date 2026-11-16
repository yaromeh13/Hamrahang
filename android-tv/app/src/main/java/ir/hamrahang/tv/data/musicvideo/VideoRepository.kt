package ir.hamrahang.tv.data.musicvideo

import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist

import ir.hamrahang.tv.domain.musicvideo.MusicVideo

import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.domain.song.SongMapper
import ir.hamrahang.tv.domain.video.VideoMapper
import ir.hamrahang.tv.domain.album.AlbumMapper
import ir.hamrahang.tv.network.ApiClient

class VideoRepository {

    suspend fun getMusicVideos(): List<MusicVideo> {
        return ApiClient.api.getLatestVideos().map { VideoMapper.map(it) }
    }

    suspend fun getTopSongs(): List<Song> {
        return ApiClient.api.getTopSongs().map { SongMapper.map(it) }
    }

    suspend fun getArtists(): List<Artist> {
        return ApiClient.api.getTopArtists().map { ArtistMapper.map(it) }
    }

    suspend fun getTrending(): List<MusicVideo> {
        return ApiClient.api.getLatestVideos().map { VideoMapper.map(it) }
    }

    suspend fun getLastAlbums(): List<Album> {
        return ApiClient.api.getLastAlbums().map { AlbumMapper.map(it) }
    }
}