package ir.hamrahang.tv.domain.album

import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.util.localizedName

object AlbumMapper {

    fun map(dto: AlbumDto): Album {
        return Album(
            id = dto.id,
            title = dto.name,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa")
        )
    }

    fun mapList(list: List<AlbumDto>) = list.map(::map)
}