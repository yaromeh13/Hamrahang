package ir.hamrahang.tv.data.local

import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.musicvideo.MusicVideoDto
import ir.hamrahang.tv.data.song.SongDto

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

                "musicvideo" -> {

                    musicVideo.syncMini(

                        MusicVideoDto(
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

    // MusicVideo

    object musicVideo : EntityUpdater<MusicVideoDto> {

        private val dao =
            DatabaseProvider
                .database()
                .musicvideoDao()

        override suspend fun syncMini(dto: MusicVideoDto) {
            sync(dto, false)
        }

        override suspend fun syncFull(dto: MusicVideoDto) {
            sync(dto, true)
        }

        private suspend fun sync(
            dto: MusicVideoDto,
            isFull: Boolean
        ) {
            val id = dto.id ?: return
            val local = dao.get(id)
            if (local?.isFull == true && !isFull) return
            val finalIsFull = (local?.isFull == true) || isFull
            val entity =
                if (local == null) {
                    MusicVideoEntityMapper.fromDto(
                        dto,
                        finalIsFull
                    )
                } else {
                    MusicVideoEntityMapper.merge(
                        local,
                        dto,
                        finalIsFull
                    )
                }
            dao.insert(entity)
        }
    }
}
