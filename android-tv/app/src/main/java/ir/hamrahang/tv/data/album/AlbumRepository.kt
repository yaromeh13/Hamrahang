package ir.hamrahang.tv.data.album

import ir.hamrahang.tv.data.local.AlbumEntityMapper
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

class AlbumRepository {

    companion object {
        private const val CACHE_TIME = 3L * 24 * 60 * 60 * 1000
    }

    private val dao = DatabaseProvider.database().albumDao()

    suspend fun getAlbum(id: String): Album {
        val local = dao.get(id)
        if (local != null && local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return AlbumEntityMapper.toDomain(local)
        }

        try {
            val response = ApiClient.api.getAlbum(id)
            val album = response.album

            album.artists?.forEach { DatabaseUpdater.artist.syncMini(it) }
            album.songs?.forEach { DatabaseUpdater.song.syncMini(it) }

            DatabaseUpdater.album.syncFull(album)
            return AlbumEntityMapper.toDomain(
                dao.get(id) ?: error("Album sync failed for id=$id")
            )
        } catch (error: CancellationException) {
            throw error
        } catch (error: Exception) {
            if (local != null) return AlbumEntityMapper.toDomain(local)
            throw error
        }
    }

    suspend fun getLatestAlbums(): List<Album> =
        fetchAlbumList { ApiClient.api.getLatestAlbums() }

    suspend fun getFeaturedAlbums(): List<Album> =
        fetchAlbumList { ApiClient.api.getFeaturedAlbums() }

    private suspend fun fetchAlbumList(
        request: suspend () -> List<AlbumDto>
    ): List<Album> {
        return try {
            val items = request()
            items.forEach { DatabaseUpdater.album.syncMini(it) }
            getByIds(items.map { it.id })
        } catch (error: CancellationException) {
            throw error
        } catch (_: Exception) {
            getAll()
        }
    }

    suspend fun getAll(): List<Album> =
        AlbumEntityMapper.toDomainList(dao.getAll())

    suspend fun getByIds(ids: List<String>): List<Album> {
        if (ids.isEmpty()) return emptyList()
        val map = dao.getByIds(ids).associateBy { it.id }
        return ids.mapNotNull { id -> map[id]?.let(AlbumEntityMapper::toDomain) }
    }
}
