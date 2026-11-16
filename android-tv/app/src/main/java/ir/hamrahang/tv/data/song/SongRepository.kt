package ir.hamrahang.tv.data.song

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.SongEntityMapper
import ir.hamrahang.tv.domain.song.Song

import ir.hamrahang.tv.network.ApiClient

class SongRepository {

    companion object {
        private const val CACHE_TIME =
            3L * 24 * 60 * 60 * 1000
    }
    private val artistRepository = ArtistRepository()
    private val dao =
        DatabaseProvider
            .database()
            .songDao()

    //==================================================
    // Detail
    //==================================================

    suspend fun getSong(
        id: String
    ): Song {

        val local = dao.get(id)

        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            val artists =
                artistRepository.getByIds(
                    SongEntityMapper.jsonToIds(local.artistIds)
                )

            return SongEntityMapper.toDomain(
                local,
                artists
            )
        }

        val response = ApiClient.api.getSong(id)

        DatabaseUpdater.artist.syncMini(response.song.artists ?: emptyList())
        DatabaseUpdater.syncParents(response.song.parents ?: emptyList())
        DatabaseUpdater.song.syncFull(response.song)

        val entity = dao.get(id)!!

        val artists =
            artistRepository.getByIds(
                SongEntityMapper.jsonToIds(entity.artistIds)
            )

        return SongEntityMapper.toDomain(
            entity,
            artists
        )
    }

    //==================================================
    // Home
    //==================================================

    suspend fun getTopSongs(): List<Song> {

        return try {

            val dto = ApiClient.api.getTopSongs()

            dto.forEach { song ->
                DatabaseUpdater.artist.syncMini(song.artists ?: emptyList())
                DatabaseUpdater.syncParents(song.parents ?: emptyList())
            }

            dto.forEach {DatabaseUpdater.song.syncMini(it)}

            getByIds(
                dto.mapNotNull {
                    it.id
                }
            )

        }

        catch (_: Exception) {

            getAll()

        }

    }

    //==================================================

    suspend fun getAll(): List<Song> {

        return dao.getAll().map { entity ->

            val artists =
                artistRepository.getByIds(
                    SongEntityMapper.jsonToIds(entity.artistIds)
                )

            SongEntityMapper.toDomain(
                entity,
                artists
            )
        }

    }

    suspend fun getByIds(
        ids: List<String>
    ): List<Song> {
        val map =
            dao.getByIds(ids)
                .associateBy {
                    it.id
                }

        return ids.mapNotNull { id ->

            val entity = map[id] ?: return@mapNotNull null

            val artists =
                artistRepository.getByIds(
                    SongEntityMapper.jsonToIds(entity.artistIds)
                )

            SongEntityMapper.toDomain(
                entity,
                artists
            )
        }

    }

    // Sync

    suspend fun sync(
        list: List<SongDto>
    ) {
        list.forEach {
            DatabaseUpdater.song.syncMini(it)
        }
    }
}
