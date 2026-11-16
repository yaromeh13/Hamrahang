package ir.hamrahang.tv.data.local

import ir.hamrahang.tv.data.local.entity.MusicVideoEntity
import ir.hamrahang.tv.data.local.entity.VideoEntity
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo

object MusicVideoEntityMapper {

    fun fromDto(dto: VideoDto, isFull: Boolean): MusicVideoEntity =
        VideoEntityMapper.fromDto(dto, isFull).toMusicVideoEntity()

    fun merge(
        old: MusicVideoEntity,
        dto: VideoDto,
        isFull: Boolean
    ): MusicVideoEntity =
        VideoEntityMapper.merge(old.toVideoEntity(), dto, isFull).toMusicVideoEntity()

    fun toDomain(
        entity: MusicVideoEntity,
        artists: List<Artist>
    ): MusicVideo =
        VideoEntityMapper.toDomain(entity.toVideoEntity(), artists)

    private fun MusicVideoEntity.toVideoEntity() = VideoEntity(
        id = id,
        title = title,
        titleFa = titleFa,
        artistIds = artistIds,
        songIds = songIds,
        meta = meta,
        sources = sources,
        isFull = isFull,
        updatedAt = updatedAt
    )

    private fun VideoEntity.toMusicVideoEntity() = MusicVideoEntity(
        id = id,
        title = title,
        titleFa = titleFa,
        artistIds = artistIds,
        songIds = songIds,
        meta = meta,
        sources = sources,
        isFull = isFull,
        updatedAt = updatedAt
    )
}
