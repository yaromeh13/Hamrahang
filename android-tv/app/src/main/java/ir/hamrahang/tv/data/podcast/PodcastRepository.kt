package ir.hamrahang.tv.data.podcast

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.PodcastEntityMapper
import ir.hamrahang.tv.data.local.entity.PodcastEntity
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

class PodcastRepository {

    companion object {
        private const val CACHE_TIME =
            3L * 24 * 60 * 60 * 1000
    }

    private val dao =
        DatabaseProvider
            .database()
            .podcastDao()

    private val artistRepository = ArtistRepository()

    suspend fun sync(items: List<PodcastDto>) {
        items.forEach { dto ->
            dto.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
            DatabaseUpdater.syncParents(dto.parents)
            DatabaseUpdater.podcast.syncMini(
                SongDto(
                    id = dto.id,
                    name = dto.name,
                    names = dto.names,
                    artists = dto.artists,
                    parents = dto.parents,
                    meta = dto.meta,
                    sources = dto.sources
                )
            )
        }
    }

    private suspend fun toDomain(entity: PodcastEntity): Podcast {
        val artists = artistRepository.getByIds(
            PodcastEntityMapper.jsonToIds(entity.artistIds)
        )
        return PodcastEntityMapper.toDomain(entity, artists)
    }

    suspend fun getPodcast(id: String): Podcast {

        val local = dao.get(id)

        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return toDomain(local)
        }

        // Song and Podcast use the same API endpoint.
        val response = try {
            ApiClient.api.getSong(id)
        } catch (error: CancellationException) {
            throw error
        } catch (error: Exception) {
            if (local != null) return toDomain(local)
            throw error
        }
        val dto = response.song

        if (dto.meta?.type != "podcast") {
            error("Requested item is not a podcast")
        }

        dto.artists?.forEach {
            DatabaseUpdater.artist.syncMini(it.toArtistDto())
        }

        DatabaseUpdater.syncParents(dto.parents)
        DatabaseUpdater.podcast.syncFull(dto)

        val entity = dao.get(id)
            ?: error("Podcast sync failed")

        return toDomain(entity)
    }

    suspend fun getAll(): List<Podcast> {
        return dao.getAll().map { toDomain(it) }
    }

    suspend fun getByIds(ids: List<String>): List<Podcast> {

        if (ids.isEmpty()) return emptyList()

        val map = dao.getByIds(ids).associateBy { it.id }

        return ids.mapNotNull { id ->
            map[id]?.let { toDomain(it) }
        }
    }
}