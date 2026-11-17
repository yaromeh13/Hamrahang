package ir.hamrahang.tv.domain.song

import ir.hamrahang.tv.data.song.SongDto

object SongMapper {
    fun map(dto: SongDto): Song = Song(
        id = dto.id ?: "",
        name = dto.name,
        names = dto.names ?: emptyMap(),
        parents = dto.parents ?: emptyList(),
        meta = dto.meta,
        sources = dto.sources ?: emptyList()
    )

    fun mapList(list: List<SongDto>): List<Song> = list.map(::map)
}
