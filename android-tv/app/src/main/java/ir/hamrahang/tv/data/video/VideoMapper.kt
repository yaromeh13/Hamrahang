package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.utils.AppState

object VideoMapper {

    fun map(dto: VideoDto): Video {

        val isFa = AppState.isPersian()

        return Video(

            id = dto.id ?: "",

            title = if (isFa)
                dto.name_fa ?: dto.name.orEmpty()
            else
                dto.name ?: dto.name_fa.orEmpty(),

            artists = dto.artists ?: emptyList(),

            songs = dto.songs ?: emptyList(),

            meta = dto.meta,

            sources = dto.sources ?: emptyList()

        )

    }

    fun mapList(
        list: List<VideoDto>
    ): List<Video> =
        list.map(::map)

}