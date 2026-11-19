package ir.hamrahang.tv.data.song

import android.util.Log
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.SongEntityMapper
import ir.hamrahang.tv.domain.song.Song

import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

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

        if (local != null && SongEntityMapper.jsonToMeta(local.meta)?.type == "podcast") {
            error("Item $id is a podcast; use PodcastRepository")
        }

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

        val response = try {
            ApiClient.api.getSong(id)
        } catch (error: CancellationException) {
            throw error
        } catch (error: Exception) {
            Log.e("DETAIL_FETCH", "Song detail request failed. id=$id", error)
            if (local != null) {
                val artists = artistRepository.getByIds(
                    SongEntityMapper.jsonToIds(local.artistIds)
                )
                return SongEntityMapper.toDomain(local, artists)
            }
            throw error
        }

        if (response.song.meta?.type == "podcast") {
            error("Item $id is a podcast; use PodcastRepository")
        }

        response.song.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
        DatabaseUpdater.syncParents(response.song.parents ?: emptyList())
        DatabaseUpdater.song.syncFull(response.song)

        val entity = dao.get(id)
            ?: error("Song sync failed for id=$id")

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
                song.artists?.forEach { DatabaseUpdater.artist.syncMini(it.toArtistDto()) }
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
