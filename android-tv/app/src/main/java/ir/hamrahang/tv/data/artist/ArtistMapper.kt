package ir.hamrahang.tv.domain.artist

import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.util.localizedName

object ArtistMapper {

    fun map(dto: ArtistDto): Artist {
        return Artist(
            id = dto.id,
            title = dto.name,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa"),
            meta = dto.meta
        )
    }

    fun mapList(list: List<ArtistDto>) = list.map(::map)
}