package ir.hamrahang.tv.data.artist

import ir.hamrahang.tv.domain.album.AlbumMapper
import ir.hamrahang.tv.domain.artist.ArtistDetails
import ir.hamrahang.tv.domain.song.SongMapper
import ir.hamrahang.tv.domain.video.VideoMapper
import ir.hamrahang.tv.util.UrlUtils
import ir.hamrahang.tv.utils.AppState

object ArtistDetailsMapper {

    fun map(dto: ArtistDetailsDto): ArtistDetails {

        val isFa = AppState.isPersian()

        return ArtistDetails(

            id = dto.id ?: "",

            name =
                if (isFa)
                    dto.name_fa ?: dto.name ?: ""
                else
                    dto.name ?: dto.name_fa ?: "",

            image =
                UrlUtils.thumbUrl(dto.thumb),

            birthDate = dto.bd,

            instagram = dto.ins,

            telegram = dto.tlg,

            albums =
                AlbumMapper.mapList(
                    dto.albums ?: emptyList()
                ),

            songs =
                SongMapper.mapList(
                    dto.songs ?: emptyList()
                ),

            videos =
                VideoMapper.mapList(
                    dto.videos ?: emptyList()
                )
        )
    }
}