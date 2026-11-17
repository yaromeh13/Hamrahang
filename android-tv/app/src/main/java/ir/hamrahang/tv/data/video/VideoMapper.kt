package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.domain.artist.ArtistMapper

object VideoMapper {

    fun map(dto: VideoDto): Video {
        val globalName = dto.name?.takeIf { it.isNotBlank() }
            ?: dto.names?.get("en")?.takeIf { it.isNotBlank() }
        val localizedNameFa = dto.name_fa?.takeIf { it.isNotBlank() }
            ?: dto.names?.get("fa")?.takeIf { it.isNotBlank() }
        val meta = dto.meta ?: MediaMeta(type = "video")

        return Video(
            id = dto.id ?: "",
            title = globalName,
            titleFa = localizedNameFa,
            url = dto.url,
            artists = dto.artists?.map { ArtistMapper.map(it.toArtistDto()) } ?: emptyList(),
            songs = dto.songs?.mapNotNull { it.id } ?: emptyList(),
            meta = meta.copy(type = meta.type ?: "video"),
            sources = dto.sources ?: emptyList()
        )
    }

    fun mapList(list: List<VideoDto>): List<Video> =
        list.map(::map)
}