package ir.hamrahang.tv.domain.song

import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.util.UrlUtils

object SongMapper {

    fun map(dto: SongDto): Song {

        return Song(
            id = dto.id ?: "",
            title = dto.name ?: "",
            titleFa = dto.name_fa ?: "",
            //url = dto.url,
            //artists = dto.artists ?: emptyList(),
            parents = dto.parents ?: emptyList(),
            meta = dto.meta,
            sources =
                dto.sources ?: emptyList()
        )

    }

    fun mapList(
        list: List<SongDto>
    ): List<Song> =
        list.map(::map)

}