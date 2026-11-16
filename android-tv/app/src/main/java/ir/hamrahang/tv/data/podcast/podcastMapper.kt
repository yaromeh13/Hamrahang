package ir.hamrahang.tv.domain.podcast

import ir.hamrahang.tv.data.podcast.PodcastDto
import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.util.UrlUtils
import ir.hamrahang.tv.utils.AppState

object PodcastMapper {

    fun map(dto: PodcastDto): Podcast {

        val isFa = AppState.isPersian()

        return Podcast(

            id = dto.id ?: "",

            title =
                if (isFa)
                    dto.name_fa ?: dto.name ?: ""
                else
                    dto.name ?: dto.name_fa ?: "",

            titleFa = dto.name_fa,

            url = dto.url,

            artists =
                dto.artists
                    ?.map { it.toArtistDto() }
                    ?.let(ArtistMapper::mapList)
                    ?: emptyList(),

            parents =
                dto.parents ?: emptyList(),

            meta =
                dto.meta?.copy(

                    tmb = UrlUtils.thumbUrl(

                        dto.meta.tmb

                    )

                ),

            sources =
                dto.sources ?: emptyList()
        )
    }

    fun mapList(list: List<PodcastDto>): List<Podcast> =
        list.map(::map)
}