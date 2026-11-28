package ir.hamrahang.tv.data.podcast
/*
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.EntityUpdater
import ir.hamrahang.tv.data.local.PodcastEntityMapper

class PodcastUpdater : EntityUpdater<PodcastDto> {

    private val dao =
        DatabaseProvider
            .database()
            .podcastDao()

    override suspend fun syncMini(dto: PodcastDto) {

        sync(dto, false)

    }

    override suspend fun syncFull(dto: PodcastDto) {

        sync(dto, true)

    }

    private suspend fun sync(
        dto: PodcastDto,
        isFull: Boolean
    ) {

        val id =
            dto.id ?: return

        val local =
            dao.get(id)

        val entity =

            if (local == null) {

                PodcastEntityMapper.fromDto(
                    dto,
                    isFull
                )

            } else {

                PodcastEntityMapper.merge(
                    local,
                    dto,
                    isFull
                )

            }

        dao.insert(entity)

    }

}*/