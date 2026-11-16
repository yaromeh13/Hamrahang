package ir.hamrahang.tv.data.musicvideo

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.local.MusicVideoEntityMapper
import ir.hamrahang.tv.data.local.SongEntityMapper
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.network.ApiClient
import kotlin.collections.forEach

class MusicVideoRepository {

    companion object {
        private const val CACHE_TIME =
            3L * 24 * 60 * 60 * 1000
    }
    private val artistRepository = ArtistRepository()
    private val dao =
        DatabaseProvider
            .database()
            .musicvideoDao()

    //==================================================
    // Detail
    //==================================================

    suspend fun getMusicVideo(
        id: String
    ): MusicVideo {

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
            return MusicVideoEntityMapper.toDomain(
                local,
                artists
            )
        }

        val response = ApiClient.api.getMusicVideo(id)

        DatabaseUpdater.artist.syncMini(response.video.artists ?: emptyList())
        //DatabaseUpdater.song.syncMini(response.musicvideo.songs ?: emptyList())
        DatabaseUpdater.musicVideo.syncFull(response.video)

        val entity = dao.get(id)!!

        val artists =
            artistRepository.getByIds(
                MusicVideoEntityMapper.jsonToIds(entity.artistIds)
            )

        return MusicVideoEntityMapper.toDomain(
            entity,
            artists
        )

    }

    //==================================================
    // Home
    //==================================================

    suspend fun getLatestMusicVideos(): List<MusicVideo> {

        return try {

            val dto = ApiClient.api.getLatestMusicVideos()

            dto.forEach { musicVideo ->
                DatabaseUpdater.artist.syncMini(musicVideo.artists ?: emptyList())
                DatabaseUpdater.song.syncMini(musicVideo.songs ?: emptyList())
            }

            dto.forEach {DatabaseUpdater.musicVideo.syncMini(it)}

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

    suspend fun getAll(): List<MusicVideo> {
        return dao.getAll().map { entity ->
            val artists =
                artistRepository.getByIds(
                    MusicVideoEntityMapper.jsonToIds(entity.artistIds)
                )
            MusicVideoEntityMapper.toDomain(
                entity,
                artists
            )
        }
    }

    suspend fun getByIds(
        ids: List<String>
    ): List<MusicVideo> {
        val map =
            dao.getByIds(ids)
                .associateBy {
                    it.id
                }

        return ids.mapNotNull { id ->

            val entity = map[id] ?: return@mapNotNull null

            val artists =
                artistRepository.getByIds(
                    MusicVideoEntityMapper.jsonToIds(entity.artistIds)
                )

            MusicVideoEntityMapper.toDomain(
                entity,
                artists
            )
        }
    }

    suspend fun sync(
        list: List<MusicVideoDto>
    ) {
        list.forEach {
            DatabaseUpdater.musicVideo.syncMini(it)
        }
    }

}