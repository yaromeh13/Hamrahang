package ir.hamrahang.tv.data.local

import ir.hamrahang.tv.data.ParentMini
import com.google.gson.Gson
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.local.entity.PodcastEntity
import ir.hamrahang.tv.data.local.entity.OwnerEntity
import ir.hamrahang.tv.data.local.VideoEntityMapper
import ir.hamrahang.tv.data.local.MusicVideoEntityMapper

object DatabaseUpdater {

    private val ownerDao = DatabaseProvider.database().ownerDao()
    private val ownerGson = Gson()

    /** The web client keeps the first cached owner for each public ID. */
    internal suspend fun syncSourceOwners(sources: List<MediaSource>?) {
        ownerDao.insertIfAbsent(
            OwnerEntity(id = 0L, name = "Hamrahang", meta = "{}", updatedAt = 0L)
        )

        sources.orEmpty().forEach { source ->
            val owner = source.owner ?: source.user
                ?.takeIf { it >= 0L }
                ?.let { SourceOwner(id = it, name = if (it == 0L) "Hamrahang" else "User #$it") }
                ?: return@forEach

            if (owner.id < 0L) return@forEach
            ownerDao.insertIfAbsent(
                OwnerEntity(
                    id = owner.id,
                    name = owner.name?.takeIf { it.isNotBlank() }
                        ?: if (owner.id == 0L) "Hamrahang" else "User #${owner.id}",
                    meta = owner.meta?.let { ownerGson.toJson(it) } ?: "{}",
                    updatedAt = System.currentTimeMillis()
                )
            )
        }
    }


    //==================================================
    // Song
    //==================================================

    object song : EntityUpdater<SongDto> {

        private val dao =
            DatabaseProvider
                .database()
                .songDao()

        override suspend fun syncMini(
            dto: SongDto
        ) {
            sync(dto, false)
        }

        override suspend fun syncFull(
            dto: SongDto
        ) {
            sync(dto, true)
        }

        private suspend fun sync(
            dto: SongDto,
            isFull: Boolean
        ) {
            if (dto.meta?.type == "podcast") {
                podcast.sync(dto, isFull)
                return
            }

            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val finalIsFull = (local?.isFull == true) || isFull
            val entity =
                if (local == null) {
                    SongEntityMapper.fromDto(
                        dto,
                        finalIsFull
                    )
                } else {
                    SongEntityMapper.merge(
                        local,
                        dto,
                        finalIsFull
                    )
                }
            dao.insert(entity)
            DatabaseUpdater.syncSourceOwners(dto.sources)
        }
    }

    //==================================================
    // Podcast
    //==================================================

    object podcast : EntityUpdater<SongDto> {

        private val dao =
            DatabaseProvider
                .database()
                .podcastDao()

        override suspend fun syncMini(dto: SongDto) {
            sync(dto, false)
        }

        override suspend fun syncFull(dto: SongDto) {
            sync(dto, true)
        }

        internal suspend fun sync(
            dto: SongDto,
            isFull: Boolean
        ) {

            if (dto.meta?.type != "podcast") return

            val id = dto.id ?: return
            val local = dao.get(id)

            if (local?.isFull == true && !isFull) return

            val finalIsFull = (local?.isFull == true) || isFull

            val entity =
                if (local == null) {
                    PodcastEntityMapper.fromDto(
                        dto,
                        finalIsFull
                    )
                } else {
                    PodcastEntityMapper.merge(
                        local,
                        dto,
                        finalIsFull
                    )
                }

            dao.insert(entity)
            syncSourceOwners(dto.sources)
        }
    }

    // Parents

    suspend fun syncParents(
        parents: List<ParentMini>?
    ) {

        parents?.forEach { parent ->

            when (parent.type) {

                "album" -> {

                    album.syncMini(

                        AlbumDto(
                            id = parent.id,
                            name = parent.name,
                            name_fa = parent.name_fa,
                            meta = parent.meta?.copy(type = parent.type)
                                ?: MediaMeta(type = parent.type)
                        )

                    )

                }

                "musicvideo", "video" -> {

                    video.syncMini(

                        VideoDto(
                            id = parent.id,
                            name = parent.name,
                            name_fa = parent.name_fa,
                            meta = parent.meta?.copy(type = parent.type)
                                ?: MediaMeta(type = parent.type)
                        )

                    )

                }

                /*
                "video" -> {
                    video.syncMini(
                        VideoDto(
                            id = parent.id,
                            name = parent.name,
                            name_fa = parent.name_fa,
                            meta = parent.meta
                        )
                    )
                }

                "podcast" -> {
                    podcast.syncMini(
                        PodcastDto(
                            id = parent.id,
                            name = parent.name,
                            name_fa = parent.name_fa,
                            meta = parent.meta
                        )
                    )
                }
                */

            }

        }

    }
    //==================================================
    // Artist
    //==================================================

    object artist : EntityUpdater<ArtistDto> {

        private val dao =
            DatabaseProvider
                .database()
                .artistDao()

        override suspend fun syncMini(
            dto: ArtistDto
        ) {

            sync(dto, false)

        }

        override suspend fun syncFull(
            dto: ArtistDto
        ) {

            sync(dto, true)

        }

        private suspend fun sync(
            dto: ArtistDto,
            isFull: Boolean
        ) {
            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val finalIsFull = (local?.isFull == true) || isFull
            val entity =
                if (local == null) {
                    ArtistEntityMapper.fromDto(
                        dto,
                        finalIsFull
                    )
                } else {
                    ArtistEntityMapper.merge(
                        old = local,
                        dto = dto,
                        isFull = finalIsFull
                    )
                }
            dao.insert(entity)
        }
    }

    // Album

    object album : EntityUpdater<AlbumDto> {

        private val dao =
            DatabaseProvider
                .database()
                .albumDao()
        override suspend fun syncMini(
            dto: AlbumDto
        ) {
            sync(dto, false)
        }

        override suspend fun syncFull(
            dto: AlbumDto
        ) {
            sync(dto, true)
        }

        private suspend fun sync(
            dto: AlbumDto,
            isFull: Boolean
        ) {
            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val finalIsFull = (local?.isFull == true) || isFull
            val entity =
                if (local == null) {
                    AlbumEntityMapper.fromDto(
                        dto,
                        finalIsFull
                    )
                } else {
                    AlbumEntityMapper.merge(
                        old = local,
                        dto = dto,
                        isFull = finalIsFull
                    )
                }
            dao.insert(entity)
        }
    }

    // Video and MusicVideo are routed into independent Room tables.

    object video : EntityUpdater<VideoDto> {
        private val dao = DatabaseProvider.database().videoDao()

        override suspend fun syncMini(dto: VideoDto) = sync(dto, false)
        override suspend fun syncFull(dto: VideoDto) = sync(dto, true)

        private suspend fun sync(dto: VideoDto, isFull: Boolean) {
            if (dto.meta?.type == "musicvideo") {
                musicvideo.sync(dto, isFull)
                return
            }
            val normalized = dto.copy(meta = (dto.meta ?: MediaMeta()).copy(type = dto.meta?.type ?: "video"))
            val id = normalized.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val full = (local?.isFull == true) || isFull
            val entity = if (local == null) {
                VideoEntityMapper.fromDto(normalized, full)
            } else {
                VideoEntityMapper.merge(local, normalized, full)
            }
            dao.insert(entity)
            DatabaseUpdater.syncSourceOwners(normalized.sources)
        }
    }

    object musicvideo : EntityUpdater<VideoDto> {
        private val dao = DatabaseProvider.database().musicVideoDao()

        override suspend fun syncMini(dto: VideoDto) = sync(dto, false)
        override suspend fun syncFull(dto: VideoDto) = sync(dto, true)

        internal suspend fun sync(dto: VideoDto, isFull: Boolean) {
            if (dto.meta?.type != "musicvideo") return
            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val full = (local?.isFull == true) || isFull
            val entity = if (local == null) {
                MusicVideoEntityMapper.fromDto(dto, full)
            } else {
                MusicVideoEntityMapper.merge(local, dto, full)
            }
            dao.insert(entity)
            DatabaseUpdater.syncSourceOwners(dto.sources)
        }
    }

}
