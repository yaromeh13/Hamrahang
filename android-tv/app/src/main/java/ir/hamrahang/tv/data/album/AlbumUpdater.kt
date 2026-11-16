package ir.hamrahang.tv.data.album

import ir.hamrahang.tv.data.local.AlbumEntityMapper
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.EntityUpdater

class AlbumUpdater : EntityUpdater<AlbumDto> {

    private val dao =
        DatabaseProvider
            .database()
            .albumDao()

    override suspend fun syncMini(dto: AlbumDto) {

        sync(dto, false)

    }

    override suspend fun syncFull(dto: AlbumDto) {

        sync(dto, true)

    }

    private suspend fun sync(
        dto: AlbumDto,
        isFull: Boolean
    ) {

        val id =
            dto.id ?: return

        val local =
            dao.get(id)

        val entity =

            if (local == null) {

                AlbumEntityMapper.fromDto(
                    dto,
                    isFull
                )

            } else {

                AlbumEntityMapper.merge(
                    local,
                    dto,
                    isFull
                )

            }

        dao.insert(entity)

    }

}