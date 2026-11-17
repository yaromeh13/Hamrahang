package ir.hamrahang.tv.domain.video

import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.domain.artist.ArtistMapper

object VideoMapper {
    fun map(dto: VideoDto): Video {
        val meta = dto.meta ?: MediaMeta(type = "video")
        return Video(
            id = dto.id ?: "",
            name = dto.name,
            names = dto.names ?: emptyMap(),
            url = dto.url,
            artists = dto.artists?.map { ArtistMapper.map(it.toArtistDto()) } ?: emptyList(),
            songs = dto.songs?.mapNotNull { it.id } ?: emptyList(),
            meta = meta.copy(type = meta.type ?: "video"),
            sources = dto.sources ?: emptyList()
        )
    }

    fun mapList(list: List<VideoDto>): List<Video> = list.map(::map)
}
