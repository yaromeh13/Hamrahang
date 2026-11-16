package ir.hamrahang.tv.data.album

import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.AlbumEntityMapper
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.network.ApiClient
import kotlin.collections.forEach

class AlbumRepository {

    companion object {
        private const val CACHE_TIME =
            3L * 24 * 60 * 60 * 1000
    }

    private val dao =
        DatabaseProvider
            .database()
            .albumDao()

    //////////////////////// Detail //////////////////

    suspend fun getAlbum(
        id: String
    ): Album {
        val local = dao.get(id)

        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return AlbumEntityMapper.toDomain(local)
        }
        val response = ApiClient.api.getAlbum(id)

        // Artists
        response.album.artists
            ?.forEach {DatabaseUpdater.artist.syncMini(it)}


        // Songs
        response.album.songs
            ?.forEach {DatabaseUpdater.song.syncMini(it)}
/*

        // MusicVideos
        response.album.musicvideos
            ?.forEach {DatabaseUpdater.musicVideo.syncMini(it)}

        // Videos
        response.album.videos
            ?.forEach {DatabaseUpdater.video.syncMini(it)}

        // Podcasts
        response.album.podcasts
            ?.forEach {DatabaseUpdater.podcast.syncMini(it)}*/

        DatabaseUpdater.album.syncFull(response.album)

        return AlbumEntityMapper.toDomain(
            dao.get(id)?: error("Album sync failed")
        )

    }

    //==================================================

    suspend fun getAll(): List<Album> {

        return AlbumEntityMapper.toDomainList(

            dao.getAll()

        )

    }

    suspend fun getByIds(
        ids: List<String>
    ): List<Album> {

        val map =
            dao.getByIds(ids)
                .associateBy { it.id }

        return ids.mapNotNull {
            map[it]?.let(
                AlbumEntityMapper::toDomain
            )
        }

    }


}