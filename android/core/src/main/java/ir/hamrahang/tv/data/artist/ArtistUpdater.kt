package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.data.local.ArtistEntityMapper
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.EntityUpdater

class ArtistUpdater : EntityUpdater<ArtistDto> {

    private val dao =
        DatabaseProvider.database().artistDao()

    override suspend fun syncMini(dto: ArtistDto) {

        sync(dto, false)

    }

    override suspend fun syncFull(dto: ArtistDto) {

        sync(dto, true)

    }

    private suspend fun sync(
        dto: ArtistDto,
        isFull: Boolean
    ) {

        val id =
            dto.id ?: return

        val local =
            dao.get(id)

        val entity =

            if (local == null) {

                ArtistEntityMapper.fromDto(
                    dto,
                    isFull
                )

            } else {

                ArtistEntityMapper.merge(
                    local,
                    dto,
                    false
                )

            }

        dao.insert(entity)

    }

}