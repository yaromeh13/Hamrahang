package ir.hamrahang.tv.data.home

import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.playback.PlaybackSource
import ir.hamrahang.tv.ui.rows.ContentRow
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.mapLatest

class HomeRepository(

    private val videoRepository: VideoRepository = VideoRepository(),
    private val musicVideoRepository: MusicVideoRepository = MusicVideoRepository(),
    private val songRepository: SongRepository = SongRepository(),
    private val artistRepository: ArtistRepository= ArtistRepository()

) {

    private val playbackHistoryRepository =
        PlaybackHistoryRepository(
            DatabaseProvider
                .database()
                .playbackHistoryDao()
        )

    // Recently Played Songs
    //==================================================

// PLAYBACK HISTORY - SONGS
    //==================================================

    fun observePlaybackHistorySongsRow(): Flow<ContentRow?> {

        return playbackHistoryRepository
            .observeLastPlayed(
                type = ContentType.SONG.value,
                userId = 0,
                limit = 30
            )
            .map { history ->

                if (history.isEmpty()) {
                    return@map null
                }

                val songs =
                    songRepository.getByIds(
                        history.map {
                            it.mediaId.toString()
                        }
                    )

                if (songs.isEmpty()) {
                    return@map null
                }

                ContentRow.Songs(

                    title = "Recently Played",

                    items = songs,

                    sessionId = "home_history_songs",

                    sessionTitle = "Recently Played",

                    sessionSource = PlaybackSource.HOME

                )

            }

    }

    //==================================================
    // PLAYBACK HISTORY - MUSIC VIDEOS
    //==================================================

    fun observePlaybackHistoryMusicVideosRow(): Flow<ContentRow?> {

        return playbackHistoryRepository
            .observeLastPlayed(
                type = ContentType.MUSICVIDEO.value,
                userId = 0,
                limit = 30
            )
            .map { history ->

                if (history.isEmpty()) {
                    return@map null
                }

                val musicVideos =
                    musicVideoRepository.getByIds(
                        history.map { it.mediaId.toString() }
                    )

                if (musicVideos.isEmpty()) {
                    return@map null
                }

                ContentRow.MusicVideos(
                    title = "Recently Played Music Videos",
                    items = musicVideos,
                    sessionId = "home_history_musicvideo",
                    sessionTitle = "Recently Played Music Videos",
                    sessionSource = PlaybackSource.HOME
                )
            }
    }

    fun observePlaybackHistoryVideosRow(): Flow<ContentRow?> {
        return playbackHistoryRepository
            .observeLastPlayed(
                type = ContentType.VIDEO.value,
                userId = 0,
                limit = 30
            )
            .map { history ->
                if (history.isEmpty()) return@map null

                val videos = videoRepository.getByIds(
                    history.map { it.mediaId.toString() }
                ).filterNot { it.isMusicVideo }

                if (videos.isEmpty()) return@map null

                ContentRow.Videos(
                    title = "Recently Played Videos",
                    items = videos,
                    sessionId = "home_history_videos",
                    sessionTitle = "Recently Played Videos",
                    sessionSource = PlaybackSource.HOME
                )
            }
    }

    // Top Songs
    //==================================================
    suspend fun buildTopSongsRow(): ContentRow? {

        return try {
            val songs = songRepository.getTopSongs()
            if (songs.isEmpty())
                null
            else

                ContentRow.Songs(

                    title = "Top Songs",

                    items = songs,

                    sessionId = "home_song",

                    sessionTitle = "Top Songs",

                    sessionSource = PlaybackSource.HOME

                )

        } catch (e: Exception) {

            null

        }

    }

    suspend fun buildMusicVideoRow(): ContentRow? {

        return try {

            val MusicVideos =
                musicVideoRepository.getLatestMusicVideos()

            if (MusicVideos.isEmpty())
                null
            else

                ContentRow.MusicVideos(

                    title = null,

                    items = MusicVideos,

                    sessionId = "home_musicvideo",

                    sessionTitle = "Music Videos",

                    sessionSource = PlaybackSource.HOME

                )

        } catch (e: Exception) {

            null

        }

    }

    suspend fun buildVideoRow(): ContentRow? {
        return try {
            val videos = videoRepository.getLatestRegularVideos()
            if (videos.isEmpty()) null
            else ContentRow.Videos(
                title = "Videos",
                items = videos,
                sessionId = "home_videos",
                sessionTitle = "Videos",
                sessionSource = PlaybackSource.HOME
            )
        } catch (_: Exception) {
            null
        }
    }

    suspend fun buildArtistRow(): ContentRow? {

        return try {

            val artists = artistRepository.getTopArtists()

            if (artists.isEmpty())
                null
            else

                ContentRow.Artists(

                    title = null,

                    items = artists,

                    sessionId = "home_artist",

                    sessionTitle = "Artists",

                    sessionSource = PlaybackSource.HOME

                )

        } catch (e: Exception) {

            null

        }

    }
/*
    suspend fun buildTrendingRow(): ContentRow? {

        return try {

            val videos =
                videoRepository.getTrending()

            if (videos.isEmpty())
                null
            else

                ContentRow.Videos(

                    title = "Trending",

                    items = videos,

                    sessionId = "home_trending",

                    sessionTitle = "Trending",

                    sessionSource = PlaybackSource.HOME

                )

        } catch (e: Exception) {

            null

        }

    }*/


    //==================================================
    // FAVORITES - HOME ROWS
    //==================================================

    fun observeFavoriteMusicVideosRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.MUSICVIDEO).mapLatest { favorites ->
            val items = musicVideoRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else ContentRow.MusicVideos(
                title = "Bookmarked Music Videos",
                items = items,
                sessionId = "home_favorite_musicvideos",
                sessionTitle = "Bookmarked Music Videos",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteSongsRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.SONG).mapLatest { favorites ->
            val items = songRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else ContentRow.Songs(
                title = "Favorite Songs",
                items = items,
                sessionId = "home_favorite_songs",
                sessionTitle = "Favorite Songs",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoritePodcastsRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.PODCAST).mapLatest { favorites ->
            val items = podcastRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else ContentRow.Podcasts(
                title = "Bookmarked Podcasts",
                items = items,
                sessionId = "home_favorite_podcasts",
                sessionTitle = "Bookmarked Podcasts",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteArtistsRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.ARTIST).mapLatest { favorites ->
            val items = artistRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else ContentRow.Artists(
                title = "Followed Artists",
                items = items,
                sessionId = "home_favorite_artists",
                sessionTitle = "Followed Artists",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteVideosRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.VIDEO).mapLatest { favorites ->
            val items = videoRepository.getByIds(favorites.map { it.mediaId.toString() })
                .filterNot { it.isMusicVideo }
            if (items.isEmpty()) null else ContentRow.Videos(
                title = "Bookmarked Videos",
                items = items,
                sessionId = "home_favorite_videos",
                sessionTitle = "Bookmarked Videos",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteAlbumsRow(): Flow<ContentRow?> =
        FavoriteManager.observeFavorites(ContentType.ALBUM).mapLatest { favorites ->
            val items = albumRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else ContentRow.Albums(
                title = "Bookmarked Albums",
                items = items,
                sessionId = "home_favorite_albums",
                sessionTitle = "Bookmarked Albums",
                sessionSource = PlaybackSource.HOME
            )
        }

}


/*package ir.hamrahang.tv.data.home

import ir.hamrahang.tv.data.musicvideo.VideoRepository
import ir.hamrahang.tv.data.song.SongRepository

class HomeRepository(

    private val videoRepository: VideoRepository = VideoRepository(),
    private val songRepository: SongRepository = SongRepository()

) {

    suspend fun getMusicVideos() =
        videoRepository.getMusicVideos()

    suspend fun getTrending() =
        videoRepository.getTrending()

    suspend fun getTopSongs() =
        songRepository.getTopSongs()

    suspend fun getArtists() =
        videoRepository.getArtists()

    suspend fun getLastAlbums() =
        videoRepository.getLastAlbums()

}*/