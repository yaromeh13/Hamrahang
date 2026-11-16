package ir.hamrahang.tv.data.podcast

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.PodcastEntityMapper
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.network.ApiClient

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

    suspend fun getPodcast(id: String): Podcast {

        val local = dao.get(id)

        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return PodcastEntityMapper.toDomain(local)
        }

        // Song and Podcast use the same API endpoint.
        val response = ApiClient.api.getSong(id)
        val dto = response.song

        if (dto.meta?.type != "podcast") {
            error("Requested item is not a podcast")
        }

        dto.artists?.forEach {
            DatabaseUpdater.artist.syncMini(it)
        }

        DatabaseUpdater.syncParents(dto.parents)
        DatabaseUpdater.podcast.syncFull(dto)

        val entity = dao.get(id)
            ?: error("Podcast sync failed")

        return PodcastEntityMapper.toDomain(entity)
    }

    suspend fun getAll(): List<Podcast> {
        return dao.getAll().map {
            PodcastEntityMapper.toDomain(it)
        }
    }

    suspend fun getByIds(ids: List<String>): List<Podcast> {

        if (ids.isEmpty()) return emptyList()

        val map = dao.getByIds(ids).associateBy { it.id }

        return ids.mapNotNull { id ->
            map[id]?.let(PodcastEntityMapper::toDomain)
        }
    }
}