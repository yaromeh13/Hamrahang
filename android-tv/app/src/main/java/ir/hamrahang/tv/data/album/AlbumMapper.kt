package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.album.AlbumDto

object AlbumMapper {
    fun map(dto: AlbumDto): Album = Album(
        id = dto.id,
        name = dto.name,
        names = dto.names ?: emptyMap(),
        artists = dto.artists?.map { it.id } ?: emptyList(),
        songs = dto.songs?.map { it.id } ?: emptyList(),
        meta = dto.meta
    )

    fun mapList(list: List<AlbumDto>) = list.map(::map)
}
