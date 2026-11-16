package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.album.AlbumDto

object AlbumMapper {

    fun map(dto: AlbumDto): Album {

        return Album(
            id = dto.id ?: "",
            title = dto.name_fa ?: dto.name ?: "",

        )
    }


    fun mapList(
        list: List<AlbumDto>
    ) = list.map(::map)
}