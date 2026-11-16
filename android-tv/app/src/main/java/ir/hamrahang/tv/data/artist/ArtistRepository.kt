package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.local.ArtistEntityMapper
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.network.ApiClient

class ArtistRepository {

    companion object {
        private const val CACHE_TIME =
            3L * 24 * 60 * 60 * 1000
    }

    private val dao =
        DatabaseProvider
            .database()
            .artistDao()

//    private val updater = DatabaseUpdater()

    //==================================================
    // Home
    //==================================================

    suspend fun getTopArtists(): List<Artist> {

        return try {

            val dto = ApiClient.api.getTopArtists()
/*
            dto.forEach { artist ->

                val id = artist.id ?: return@forEach

                val local = dao.get(id)

                if (local == null) {

                    dao.insert(

                        ArtistEntityMapper.fromDto(

                            dto = artist,
                            isFull = false

                        )

                    )

                }

            }
*/
            dto.forEach {

                DatabaseUpdater.artist.syncMini(it)

            }

            ArtistEntityMapper.toDomainList(

                dao.getAll()

            )

        } catch (_: Exception) {

            ArtistEntityMapper.toDomainList(

                dao.getAll()

            )

        }

    }

    //==================================================
    // Detail
    //==================================================

    suspend fun getArtist(
        id: String
    ): Artist {

        val local = dao.get(id)

        if (
            local != null &&
            local.isFull &&
            System.currentTimeMillis() - local.updatedAt < CACHE_TIME
        ) {
            return ArtistEntityMapper.toDetails(local)
        }

        val response = ApiClient.api.getArtist(id)

        // Albums
        response.artist.albums?.
            forEach{DatabaseUpdater.album.syncMini(it)}

        // Songs
        response.artist.songs?.
            forEach {DatabaseUpdater.song.syncMini(it)}

        response.artist.podcasts?.forEach {
            DatabaseUpdater.podcast.syncMini(it)
        }

        // Videos and MusicVideos share the same API and Room table.
        response.artist.musicvideos?.forEach {
            DatabaseUpdater.video.syncMini(it)
        }

        response.artist.videos?.forEach {
            DatabaseUpdater.video.syncMini(it)
        }

        // ---------- Sync child tables ----------
/*
        updater.updateAlbums(
            response.artist.albums ?: emptyList()
        )

        updater.updateSongs(
            response.artist.songs ?: emptyList()
        )

        updater.updateMusicVideos(
            response.artist.musicvideos ?: emptyList()
        )

        updater.updateVideos(
            response.artist.videos ?: emptyList()
        )

        updater.updatePodcasts(
            response.artist.podcasts ?: emptyList()
        )
*/

        DatabaseUpdater.artist.syncFull(response.artist)

        return ArtistEntityMapper.toDetails(
            dao.get(id)?: error("Artist sync failed")
        )
    }

    //==================================================

    suspend fun getByIds(
        ids: List<String>
    ): List<Artist> {

        val map =
            dao.getByIds(ids)
                .associateBy { it.id }

        return ids.mapNotNull {
            map[it]?.let(
                ArtistEntityMapper::toDomain
            )
        }
    }

}