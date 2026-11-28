package ir.hamrahang.tv.data.video
/*
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.EntityUpdater
import ir.hamrahang.tv.data.local.VideoEntityMapper

class VideoUpdater : EntityUpdater<VideoDto> {

    private val dao =
        DatabaseProvider
            .database()
            .videoDao()

    override suspend fun syncMini(dto: VideoDto) {

        sync(dto, false)

    }

    override suspend fun syncFull(dto: VideoDto) {

        sync(dto, true)

    }

    private suspend fun sync(
        dto: VideoDto,
        isFull: Boolean
    ) {

        val id =
            dto.id ?: return

        val local =
            dao.get(id)

        val entity =

            if (local == null) {

                VideoEntityMapper.fromDto(
                    dto,
                    isFull
                )

            } else {

                VideoEntityMapper.merge(
                    local,
                    dto,
                    isFull
                )

            }

        dao.insert(entity)

    }

}*/