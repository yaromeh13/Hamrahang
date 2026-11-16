package ir.hamrahang.tv.data.musicvideo

import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.domain.musicvideo.MusicVideo

@Deprecated("Use VideoRepository for videos and music videos")
class MusicVideoRepository(
    private val repository: VideoRepository = VideoRepository()
) {
    suspend fun getMusicVideo(id: String): MusicVideo =
        repository.getVideo(id).also {
            if (!it.isMusicVideo) error("Requested item is not a music video")
        }

    suspend fun getLatestMusicVideos(): List<MusicVideo> =
        repository.getLatestMusicVideos()

    suspend fun getAll(): List<MusicVideo> =
        repository.getAll().filter { it.isMusicVideo }

    suspend fun getByIds(ids: List<String>): List<MusicVideo> =
        repository.getByIds(ids).filter { it.isMusicVideo }

    suspend fun sync(list: List<MusicVideoDto>) =
        repository.sync(list)
}
