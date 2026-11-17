package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.album.AlbumDto

object AlbumMapper {
    fun map(dto: AlbumDto): Album = Album(
        id = dto.id,
        name = dto.name,
        names = dto.names ?: emptyMap()
    )

    fun mapList(list: List<AlbumDto>) = list.map(::map)
}
