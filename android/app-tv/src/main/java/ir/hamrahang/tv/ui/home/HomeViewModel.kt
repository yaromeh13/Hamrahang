package ir.hamrahang.tv.ui.home

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.home.HomeRepository
import ir.hamrahang.tv.domain.home.HomeFeedSection
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import ir.hamrahang.tv.ui.rows.ContentRow
import ir.hamrahang.tv.R
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.mapLatest
import kotlinx.coroutines.launch
import kotlinx.coroutines.Job

class HomeViewModel(application: Application) : AndroidViewModel(application) {

    private val repo = HomeRepository()
    private val playlistRepository = PlaylistRepository(application)

    private val _rows = MutableStateFlow<List<ContentRow>>(emptyList())
    private val _playlists = MutableStateFlow<List<PlaylistEntity>>(emptyList())
    private var rowsJob: Job? = null

    val rows: StateFlow<List<ContentRow>>
        get() = _rows

    init {
        refreshContent()
        refreshPlaylists()
    }

    /**
     * Read the current account's local playlists when Home becomes visible.
     * This also handles login/session changes that do not modify the Room table.
     */
    fun refreshPlaylists() {
        viewModelScope.launch {
            // Render the Room cache immediately; network refresh must not delay Home.
            _playlists.value = runCatching {
                playlistRepository.getPlaylists()
            }.getOrDefault(emptyList())

            runCatching { playlistRepository.refreshFromServer() }
            _playlists.value = runCatching {
                playlistRepository.getPlaylists()
            }.getOrDefault(emptyList())
        }
    }

    /** Rebuild visible Home rows after the API becomes reachable. */
    fun refreshContent() {
        rowsJob?.cancel()
        rowsJob = viewModelScope.launch {
            val mainRows = buildList {
                repo.buildVideoRow()?.let { add(it.toContentRow()) }
                repo.buildMusicVideoRow()?.let { add(it.toContentRow()) }
                repo.buildTopSongsRow()?.let { add(it.toContentRow()) }
                repo.buildArtistRow()?.let { add(it.toContentRow()) }
            }

            val historyRows = combine(
                repo.observePlaybackHistorySongsRow().map { it?.toContentRow() },
                repo.observePlaybackHistoryMusicVideosRow().map { it?.toContentRow() },
                repo.observePlaybackHistoryVideosRow().map { it?.toContentRow() }
            ) { songs, musicVideos, videos ->
                listOfNotNull(songs, musicVideos, videos)
            }

            val favoriteMusicAndSongs = combine(
                repo.observeFavoriteMusicVideosRow().map { it?.toContentRow() },
                repo.observeFavoriteSongsRow().map { it?.toContentRow() }
            ) { musicVideos, songs ->
                listOfNotNull(musicVideos, songs)
            }

            val favoritePodcastsAndArtists = combine(
                repo.observeFavoritePodcastsRow().map { it?.toContentRow() },
                repo.observeFavoriteArtistsRow().map { it?.toContentRow() }
            ) { podcasts, artists ->
                listOfNotNull(podcasts, artists)
            }

            val favoriteVideosAndAlbums = combine(
                repo.observeFavoriteVideosRow().map { it?.toContentRow() },
                repo.observeFavoriteAlbumsRow().map { it?.toContentRow() }
            ) { videos, albums ->
                listOfNotNull(videos, albums)
            }

            val playlistRows = _playlists.map { playlists ->
                if (playlists.isEmpty()) emptyList()
                else listOf(
                    ContentRow.Playlists(
                        title = getApplication<Application>().getString(R.string.your_playlists),
                        items = playlists,
                        sessionId = "home_playlists",
                        sessionTitle = getApplication<Application>().getString(R.string.your_playlists),
                        sessionSource = ir.hamrahang.tv.playback.PlaybackSource.HOME
                    )
                )
            }

            val favoritePlaylists = FavoriteManager
                .observeFavorites(ContentType.PLAYLIST)
                .mapLatest { favorites ->
                    playlistRepository.getFavoritePlaylists(
                        favorites.map { it.mediaId.toString() }
                    )
                }

            val favoritePlaylistRow = favoritePlaylists.map { playlists ->
                if (playlists.isEmpty()) emptyList()
                else listOf(
                    ContentRow.Playlists(
                        title = getApplication<Application>().getString(R.string.bookmarked_playlists),
                        items = playlists,
                        sessionId = "home_favorite_playlists",
                        sessionTitle = getApplication<Application>().getString(R.string.bookmarked_playlists),
                        sessionSource = ir.hamrahang.tv.playback.PlaybackSource.HOME
                    )
                )
            }

            val favoriteRows = combine(
                favoriteMusicAndSongs,
                favoritePodcastsAndArtists,
                favoriteVideosAndAlbums
            ) { first, second, third ->
                first + second + third
            }

            combine(historyRows, playlistRows, favoriteRows, favoritePlaylistRow) {
                    history, playlists, favorites, bookmarkedPlaylists ->
                mainRows + history + playlists + favorites + bookmarkedPlaylists
            }.collect { rows ->
                _rows.value = rows
            }
        }
    }
}


private fun HomeFeedSection.toContentRow(): ContentRow = when (this) {
    is HomeFeedSection.MusicVideos -> ContentRow.MusicVideos(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
    is HomeFeedSection.Songs -> ContentRow.Songs(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
    is HomeFeedSection.Artists -> ContentRow.Artists(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
    is HomeFeedSection.Albums -> ContentRow.Albums(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
    is HomeFeedSection.Podcasts -> ContentRow.Podcasts(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
    is HomeFeedSection.Videos -> ContentRow.Videos(
        title = title,
        items = items,
        sessionId = sessionId,
        sessionTitle = sessionTitle,
        sessionSource = sessionSource
    )
}
