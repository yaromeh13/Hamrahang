package ir.hamrahang.tv.data.video


import ir.hamrahang.tv.domain.video.VideoMapper
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.network.ApiClient

class VideoRepository {

    suspend fun getVideos(): List<Video> {
        return ApiClient.api.getLatestVideos().map { VideoMapper.map(it) }
    }
/*
    suspend fun getByIds(
        ids: List<String>
    ): List<Video> {

        val map =
            dao.getByIds(ids)
                .associateBy { it.id }

        return ids.mapNotNull {
            map[it]?.let(
                VideoEntityMapper::toDomain
            )
        }

    }*/
}