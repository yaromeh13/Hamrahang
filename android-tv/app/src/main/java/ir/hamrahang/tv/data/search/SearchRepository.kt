package ir.hamrahang.tv.data.search

import ir.hamrahang.tv.data.album.AlbumRepository

import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository

import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.network.ApiClient

class SearchRepository(


    private val albumRepository: AlbumRepository = AlbumRepository(),
    private val musicVideoRepository: MusicVideoRepository = MusicVideoRepository(),
    private val podcastRepository: PodcastRepository = PodcastRepository()

) {

    suspend fun search(query: String): SearchResponseDto {

        val response = ApiClient.api.search(query)

        DatabaseUpdater.artist.syncMini(
            response.search.artists ?: emptyList()
        )

        DatabaseUpdater.album.syncMini(
            response.search.albums ?: emptyList()
        )

        DatabaseUpdater.song.syncMini(
            response.search.songs ?: emptyList()
        )

        DatabaseUpdater.musicVideo.syncMini(
            response.search.musicvideos ?: emptyList()
        )

        podcastRepository.sync(response.search.podcasts ?: emptyList())

        //response.search.videos?.let {
        //    videoRepository.sync(it)
        //}

        return response
    }
}
