package ir.hamrahang.tv.domain.video

import android.provider.MediaStore
import ir.hamrahang.tv.data.musicvideo.VideoDto
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.util.UrlUtils
import ir.hamrahang.tv.utils.AppState

object VideoMapper {

    fun map(dto: VideoDto): MusicVideo {
        val isFa = AppState.isPersian()

        return MusicVideo(
            id = dto.id ?: "",
            title = if (isFa) dto.name_fa ?: dto.name ?: "" else dto.name ?: dto.name_fa ?: "",
            artist = if (isFa) dto.artist_fa ?: dto.artist ?: "" else dto.artist ?: dto.artist_fa ?: "",
            thumbUrl = UrlUtils.thumbUrl(dto.thumb),
            vdoUrl = dto.video?.firstOrNull()?.url


        )

    }

    fun mapList(list: List<VideoDto>): List<MusicVideo> {
        return list.map { map(it) }
    }
}