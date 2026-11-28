package ir.hamrahang.tv.domain.favorite

import ir.hamrahang.tv.data.favorite.FavoriteResponseDto
import ir.hamrahang.tv.data.favorite.FavoriteSection
import ir.hamrahang.tv.domain.album.AlbumMapper
import ir.hamrahang.tv.domain.artist.ArtistMapper
import ir.hamrahang.tv.domain.song.SongMapper
import ir.hamrahang.tv.domain.video.VideoMapper

object FavoriteMapper {

    fun map(dto: FavoriteResponseDto): List<FavoriteSection> {
        val allVideos = (dto.videos + dto.musicvideos).distinctBy { it.id }
        val videos = VideoMapper.mapList(allVideos).filterNot { it.isMusicVideo }
        val musicVideos = VideoMapper.mapList(allVideos).filter { it.isMusicVideo }

        return listOf(
            FavoriteSection.Artists(ArtistMapper.mapList(dto.artists)),
            FavoriteSection.Songs(SongMapper.mapList(dto.songs)),
            FavoriteSection.Albums(AlbumMapper.mapList(dto.albums)),
            FavoriteSection.Videos(videos),
            FavoriteSection.MusicVideos(musicVideos)
        ).filter { section ->
            when (section) {
                is FavoriteSection.Artists -> section.items.isNotEmpty()
                is FavoriteSection.Songs -> section.items.isNotEmpty()
                is FavoriteSection.Albums -> section.items.isNotEmpty()
                is FavoriteSection.Videos -> section.items.isNotEmpty()
                is FavoriteSection.MusicVideos -> section.items.isNotEmpty()
            }
        }
    }
}
