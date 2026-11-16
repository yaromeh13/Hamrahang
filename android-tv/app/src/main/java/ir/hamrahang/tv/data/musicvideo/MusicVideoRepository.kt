package ir.hamrahang.tv.data.musicvideo

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.MusicVideoEntityMapper
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

class MusicVideoRepository {

    companion object {
        private const val CACHE_TIME = 3L * 24 * 60 * 60 * 1000
    }

    private val dao = DatabaseProvider.database().musicVideoDao()
    private val artistRepository = ArtistRepository()
    private var latestCache: List<MusicVideo>? = null
    private var latestCacheAt = 0L

    suspend fun sync(items: List<VideoDto>) {
        items.filter { it.meta?.type == "musicvideo" }.forEach { dto ->
            dto.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
            dto.songs?.forEach { DatabaseUpdater.song.syncMini(it) }
            DatabaseUpdater.musicvideo.syncMini(dto)
        }
    }

    suspend fun getMusicVideo(id: String): MusicVideo {
        val local = dao.get(id)
        if (local != null && local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) return toDomain(local)

        val dto = try {
            ApiClient.api.getVideo(id).video
        } catch (error: CancellationException) {
            throw error
        } catch (error: Exception) {
            if (local != null) return toDomain(local)
            throw error
        }

        if (dto.meta?.type != "musicvideo") {
            error("Item $id is not a music video")
        }
        dto.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
        dto.songs?.forEach { DatabaseUpdater.song.syncMini(it) }
        DatabaseUpdater.musicvideo.syncFull(dto)

        return toDomain(dao.get(id) ?: error("MusicVideo sync failed for id=$id"))
    }

    suspend fun getLatestMusicVideos(): List<MusicVideo> {
        val now = System.currentTimeMillis()
        latestCache?.let { if (now - latestCacheAt < 30_000L) return it }

        val result = try {
            val items = ApiClient.api.getLatestVideos().filter { it.meta?.type == "musicvideo" }
            sync(items)
            getByIds(items.mapNotNull { it.id })
        } catch (error: CancellationException) {
            throw error
        } catch (_: Exception) {
            getAll()
        }
        latestCache = result
        latestCacheAt = now
        return result
    }

    suspend fun getAll(): List<MusicVideo> =
        dao.getAll().map { toDomain(it) }

    suspend fun getByIds(ids: List<String>): List<MusicVideo> {
        if (ids.isEmpty()) return emptyList()
        val map = dao.getByIds(ids).associateBy { it.id }
        return ids.mapNotNull { id -> map[id]?.let { toDomain(it) } }
    }

    private suspend fun toDomain(entity: ir.hamrahang.tv.data.local.entity.MusicVideoEntity): MusicVideo {
        val artists = artistRepository.getByIds(MusicVideoEntityMapper.jsonToIds(entity.artistIds))
        return MusicVideoEntityMapper.toDomain(entity, artists)
    }
}
