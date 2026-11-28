package ir.hamrahang.tv.domain.artist

import ir.hamrahang.tv.data.artist.ArtistDto

object ArtistMapper {
    fun map(dto: ArtistDto): Artist = Artist(
        id = dto.id,
        name = dto.name,
        names = dto.names ?: emptyMap(),
        meta = dto.meta
    )

    fun mapList(list: List<ArtistDto>) = list.map(::map)
}
