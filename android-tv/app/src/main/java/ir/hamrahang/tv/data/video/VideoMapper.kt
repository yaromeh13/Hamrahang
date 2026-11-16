package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.utils.AppState

object VideoMapper {

    fun map(dto: VideoDto): Video {
        val isFa = AppState.isPersian()

        val title = dto.name ?: dto.names?.get("en")
        val titleFa = dto.name_fa ?: dto.names?.get("fa")

        return Video(
            id = dto.id ?: "",
            title = if (isFa) titleFa ?: title else title ?: titleFa,
            titleFa = titleFa,
            url = dto.url,
            artists = dto.artists?.let(ArtistMapper::mapList) ?: emptyList(),
            songs = dto.songs?.mapNotNull { it.id } ?: emptyList(),
            meta = dto.meta,
            sources = dto.sources ?: emptyList()
        )
    }

    fun mapList(list: List<VideoDto>): List<Video> =
        list.map(::map)
}
