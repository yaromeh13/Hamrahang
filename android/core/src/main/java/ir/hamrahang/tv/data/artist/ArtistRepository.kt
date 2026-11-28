package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.local.ArtistEntityMapper
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

class ArtistRepository {

    companion object {
        private const val CACHE_TIME = 3L * 24 * 60 * 60 * 1000
    }

    private val dao = DatabaseProvider.database().artistDao()

    //==================================================
    // Home
    //==================================================

    suspend fun getTopArtists(): List<Artist> {
        return try {
            val items = ApiClient.api.getTopArtists()
            items.forEach { DatabaseUpdater.artist.syncMini(it) }
            getByIds(items.map { it.id })
        } catch (error: CancellationException) {
            throw error
        } catch (_: Exception) {
            ArtistEntityMapper.toDomainList(dao.getAll())
        }
    }

    //==================================================
    // Detail
    //==================================================

    suspend fun getArtist(id: String): Artist {
        val local = dao.get(id)
        if (local != null && local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return ArtistEntityMapper.toDetails(local)
        }

        try {
            val response = ApiClient.api.getArtist(id)
            val artist = response.artist

            // Related albums and tracks are included in the detail response.
            artist.albums?.forEach { DatabaseUpdater.album.syncMini(it) }
            artist.songs?.forEach { DatabaseUpdater.song.syncMini(it) }
            artist.podcasts?.forEach { DatabaseUpdater.podcast.syncMini(it) }

            // Keep each video category in its own Room table.
            MusicVideoRepository().sync(artist.musicvideos ?: emptyList())
            artist.videos?.forEach { DatabaseUpdater.video.syncMini(it) }

            DatabaseUpdater.artist.syncFull(artist)

            return ArtistEntityMapper.toDetails(
                dao.get(id) ?: error("Artist sync failed for id=$id")
            )
        } catch (error: CancellationException) {
            throw error
        } catch (error: Exception) {
            if (local != null) return ArtistEntityMapper.toDetails(local)
            throw error
        }
    }

    suspend fun getByIds(ids: List<String>): List<Artist> {
        if (ids.isEmpty()) return emptyList()
        val map = dao.getByIds(ids).associateBy { it.id }
        return ids.mapNotNull { id -> map[id]?.let(ArtistEntityMapper::toDomain) }
    }
}
