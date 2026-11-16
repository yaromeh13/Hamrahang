package ir.hamrahang.tv.data.local

import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.data.local.entity.PodcastEntity
import ir.hamrahang.tv.data.local.entity.VideoEntity

object DatabaseUpdater {

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
                            meta = parent.meta
                        )

                    )

                }

                "musicvideo", "video" -> {

                    video.syncMini(

                        VideoDto(
                            id = parent.id,
                            name = parent.name,
                            name_fa = parent.name_fa,
                            meta = parent.meta
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

    // Video and MusicVideo share the videos table

    object video : EntityUpdater<VideoDto> {

        private val dao =
            DatabaseProvider.database().videoDao()

        override suspend fun syncMini(dto: VideoDto) {
            sync(dto, false)
        }

        override suspend fun syncFull(dto: VideoDto) {
            sync(dto, true)
        }

        private suspend fun sync(dto: VideoDto, isFull: Boolean) {
            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return

            val finalIsFull = (local?.isFull == true) || isFull
            val entity = if (local == null) {
                VideoEntityMapper.fromDto(dto, finalIsFull)
            } else {
                VideoEntityMapper.merge(local, dto, finalIsFull)
            }
            dao.insert(entity)
        }
    }

}
