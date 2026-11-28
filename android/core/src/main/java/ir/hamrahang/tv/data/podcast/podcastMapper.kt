package ir.hamrahang.tv.domain.podcast

import ir.hamrahang.tv.data.podcast.PodcastDto
import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.util.UrlUtils

object PodcastMapper {
    fun map(dto: PodcastDto): Podcast = Podcast(
        id = dto.id ?: "",
        name = dto.name,
        names = dto.names ?: emptyMap(),
        url = dto.url,
        artists = dto.artists?.map { it.toArtistDto() }?.let(ArtistMapper::mapList) ?: emptyList(),
        parents = dto.parents ?: emptyList(),
        meta = dto.meta?.copy(tmb = UrlUtils.thumbUrl(dto.meta.tmb)),
        sources = dto.sources ?: emptyList()
    )

    fun mapList(list: List<PodcastDto>): List<Podcast> = list.map(::map)
}
