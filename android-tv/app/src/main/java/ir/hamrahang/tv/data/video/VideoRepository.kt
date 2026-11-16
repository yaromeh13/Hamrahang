package ir.hamrahang.tv.data.video

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.VideoEntityMapper
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.network.ApiClient

class VideoRepository {

    companion object {
        private const val CACHE_TIME = 3L * 24 * 60 * 60 * 1000
    }

    private val dao = DatabaseProvider.database().videoDao()
    private val artistRepository = ArtistRepository()
    private var latestCache: List<Video>? = null
    private var latestCacheAt: Long = 0L

    suspend fun sync(items: List<VideoDto>) {
        items.forEach { dto ->
            dto.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
            dto.songs?.forEach { DatabaseUpdater.song.syncMini(it) }
            DatabaseUpdater.video.syncMini(normalize(dto))
        }
    }

    suspend fun getVideo(id: String): Video {
        val local = dao.get(id)
        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return toDomain(local)
        }

        val dto = try {
            normalize(ApiClient.api.getVideo(id).video)
        } catch (error: Exception) {
            // Keep locally cached video details usable when the API is unavailable.
            if (local != null) return toDomain(local)
            throw error
        }

        dto.artists?.forEach { DatabaseUpdater.artist.syncMini(it) }
        dto.songs?.forEach { DatabaseUpdater.song.syncMini(it) }
        DatabaseUpdater.video.syncFull(dto)

        val entity = dao.get(id) ?: error("Video sync failed for id=$id")
        return toDomain(entity)
    }

    suspend fun getLatestVideos(): List<Video> {
        val now = System.currentTimeMillis()
        latestCache?.let {
            if (now - latestCacheAt < 30_000L) return it
        }

        val result = try {
            val dto = ApiClient.api.getLatestVideos().map(::normalize)
            sync(dto)
            getByIds(dto.mapNotNull { it.id })
        } catch (_: Exception) {
            getAll()
        }

        latestCache = result
        latestCacheAt = now
        return result
    }

    suspend fun getLatestMusicVideos(): List<Video> =
        getLatestVideos().filter { it.isMusicVideo }

    suspend fun getLatestRegularVideos(): List<Video> =
        getLatestVideos().filterNot { it.isMusicVideo }

    suspend fun getAll(): List<Video> =
        dao.getAll().map { toDomain(it) }

    suspend fun getByIds(ids: List<String>): List<Video> {
        if (ids.isEmpty()) return emptyList()
        val entities = dao.getByIds(ids).associateBy { it.id }
        return ids.mapNotNull { id -> entities[id]?.let { toDomain(it) } }
    }

    private suspend fun toDomain(entity: ir.hamrahang.tv.data.local.entity.VideoEntity): Video {
        val artists = artistRepository.getByIds(
            VideoEntityMapper.jsonToIds(entity.artistIds)
        )
        return VideoEntityMapper.toDomain(entity, artists)
    }

    private fun normalize(dto: VideoDto): VideoDto {
        val meta = dto.meta ?: ir.hamrahang.tv.data.MediaMeta()
        return dto.copy(meta = meta.copy(type = meta.type ?: "musicvideo"))
    }
}
