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
import ir.hamrahang.tv.domain.home.HomeFeedSection
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.mapLatest

class HomeRepository(

    private val videoRepository: VideoRepository = VideoRepository(),
    private val musicVideoRepository: MusicVideoRepository = MusicVideoRepository(),
    private val songRepository: SongRepository = SongRepository(),
    private val artistRepository: ArtistRepository = ArtistRepository(),
    private val podcastRepository: PodcastRepository = PodcastRepository(),
    private val albumRepository: AlbumRepository = AlbumRepository()

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

    fun observePlaybackHistorySongsRow(): Flow<HomeFeedSection?> {

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

                HomeFeedSection.Songs(

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

    fun observePlaybackHistoryMusicVideosRow(): Flow<HomeFeedSection?> {

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

                HomeFeedSection.MusicVideos(
                    title = "Recently Played Music Videos",
                    items = musicVideos,
                    sessionId = "home_history_musicvideo",
                    sessionTitle = "Recently Played Music Videos",
                    sessionSource = PlaybackSource.HOME
                )
            }
    }

    fun observePlaybackHistoryVideosRow(): Flow<HomeFeedSection?> {
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

                HomeFeedSection.Videos(
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
    suspend fun buildTopSongsRow(): HomeFeedSection? {

        return try {
            val songs = songRepository.getTopSongs()
            if (songs.isEmpty())
                null
            else

                HomeFeedSection.Songs(

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

    suspend fun buildMusicVideoRow(): HomeFeedSection? {

        return try {

            val MusicVideos =
                musicVideoRepository.getLatestMusicVideos()

            if (MusicVideos.isEmpty())
                null
            else

                HomeFeedSection.MusicVideos(

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

    suspend fun buildVideoRow(): HomeFeedSection? {
        return try {
            val videos = videoRepository.getLatestRegularVideos()
            if (videos.isEmpty()) null
            else HomeFeedSection.Videos(
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

    suspend fun buildArtistRow(): HomeFeedSection? {

        return try {

            val artists = artistRepository.getTopArtists()

            if (artists.isEmpty())
                null
            else

                HomeFeedSection.Artists(

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
    suspend fun buildTrendingRow(): HomeFeedSection? {

        return try {

            val videos =
                videoRepository.getTrending()

            if (videos.isEmpty())
                null
            else

                HomeFeedSection.Videos(

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

    fun observeFavoriteMusicVideosRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.MUSICVIDEO).mapLatest { favorites ->
            val items = musicVideoRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else HomeFeedSection.MusicVideos(
                title = "Favorite Music Videos",
                items = items,
                sessionId = "home_favorite_musicvideos",
                sessionTitle = "Favorite Music Videos",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteSongsRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.SONG).mapLatest { favorites ->
            val items = songRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else HomeFeedSection.Songs(
                title = "Favorite Songs",
                items = items,
                sessionId = "home_favorite_songs",
                sessionTitle = "Favorite Songs",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoritePodcastsRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.PODCAST).mapLatest { favorites ->
            val items = podcastRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else HomeFeedSection.Podcasts(
                title = "Bookmarked Podcasts",
                items = items,
                sessionId = "home_favorite_podcasts",
                sessionTitle = "Bookmarked Podcasts",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteArtistsRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.ARTIST).mapLatest { favorites ->
            val items = artistRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else HomeFeedSection.Artists(
                title = "Followed Artists",
                items = items,
                sessionId = "home_favorite_artists",
                sessionTitle = "Followed Artists",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteVideosRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.VIDEO).mapLatest { favorites ->
            val items = videoRepository.getByIds(favorites.map { it.mediaId.toString() })
                .filterNot { it.isMusicVideo }
            if (items.isEmpty()) null else HomeFeedSection.Videos(
                title = "Bookmarked Videos",
                items = items,
                sessionId = "home_favorite_videos",
                sessionTitle = "Bookmarked Videos",
                sessionSource = PlaybackSource.HOME
            )
        }

    fun observeFavoriteAlbumsRow(): Flow<HomeFeedSection?> =
        FavoriteManager.observeFavorites(ContentType.ALBUM).mapLatest { favorites ->
            val items = albumRepository.getByIds(favorites.map { it.mediaId.toString() })
            if (items.isEmpty()) null else HomeFeedSection.Albums(
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