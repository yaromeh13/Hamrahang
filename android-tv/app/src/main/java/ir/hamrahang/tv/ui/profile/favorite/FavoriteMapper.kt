package ir.hamrahang.tv.domain.favorite

import ir.hamrahang.tv.data.favorite.FavoriteResponseDto
import ir.hamrahang.tv.data.favorite.FavoriteSection
import ir.hamrahang.tv.domain.album.AlbumMapper
import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.domain.song.SongMapper
import ir.hamrahang.tv.domain.video.VideoMapper

object FavoriteMapper {

    fun map(dto: FavoriteResponseDto): List<FavoriteSection> {

        return listOf(

            FavoriteSection.Artists(
                ArtistMapper.mapList(dto.artists)
            ),

            FavoriteSection.Songs(
                SongMapper.mapList(dto.songs)
            ),

            FavoriteSection.Albums(
                AlbumMapper.mapList(dto.albums)
            ),

            /*FavoriteSection.Videos(
                VideoMapper.mapList(dto.videos)
            )*/

        ).filter {

            when (it) {

                is FavoriteSection.Artists -> it.items.isNotEmpty()

                is FavoriteSection.Songs -> it.items.isNotEmpty()

                is FavoriteSection.Albums -> it.items.isNotEmpty()

                is FavoriteSection.Videos -> it.items.isNotEmpty()

                is FavoriteSection.MusicVideos -> it.items.isNotEmpty()

            }

        }

    }

}