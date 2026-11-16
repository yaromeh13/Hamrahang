package ir.hamrahang.tv.domain.artist

import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.util.UrlUtils

object ArtistMapper {

    fun map(dto: ArtistDto): Artist {

        return Artist(
            id = dto.id ?: "",
            title = dto.name ?: "",
            titleFa = dto.name_fa ?: "",
            //url = dto.url,
            meta =dto.meta
        )
    }

    fun mapList(list: List<ArtistDto>) =
        list.map(::map)
}