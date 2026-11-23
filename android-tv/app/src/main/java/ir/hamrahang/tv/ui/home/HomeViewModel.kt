package ir.hamrahang.tv.ui.home

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.home.HomeRepository
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import ir.hamrahang.tv.ui.rows.ContentRow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch

class HomeViewModel(application: Application) : AndroidViewModel(application) {

    private val repo = HomeRepository()
    private val playlistRepository = PlaylistRepository(application)

    private val _rows = MutableStateFlow<List<ContentRow>>(emptyList())
    private val _playlists = MutableStateFlow<List<PlaylistEntity>>(emptyList())

    val rows: StateFlow<List<ContentRow>>
        get() = _rows

    init {
        loadRows()
        refreshPlaylists()
    }

    /**
     * Reload local playlists first, so Home can render cached data even when
     * the server is unavailable or the account session was restored late.
     */
    fun refreshPlaylists() {
        viewModelScope.launch {
            // Settings/login synchronization owns network refresh. Home reads the
            // account-scoped Room cache only, avoiding duplicate API requests.
            _playlists.value = runCatching {
                playlistRepository.getPlaylists()
            }.getOrDefault(emptyList())
        }
    }

    private fun loadRows() {
        viewModelScope.launch {
            val mainRows = buildList {
                repo.buildVideoRow()?.let(::add)
                repo.buildMusicVideoRow()?.let(::add)
                repo.buildTopSongsRow()?.let(::add)
                repo.buildArtistRow()?.let(::add)
            }

            val historyRows = combine(
                repo.observePlaybackHistorySongsRow(),
                repo.observePlaybackHistoryMusicVideosRow(),
                repo.observePlaybackHistoryVideosRow()
            ) { songs, musicVideos, videos ->
                listOfNotNull(songs, musicVideos, videos)
            }

            val favoriteMusicAndSongs = combine(
                repo.observeFavoriteMusicVideosRow(),
                repo.observeFavoriteSongsRow()
            ) { musicVideos, songs ->
                listOfNotNull(musicVideos, songs)
            }

            val favoritePodcastsAndArtists = combine(
                repo.observeFavoritePodcastsRow(),
                repo.observeFavoriteArtistsRow()
            ) { podcasts, artists ->
                listOfNotNull(podcasts, artists)
            }

            val favoriteVideosAndAlbums = combine(
                repo.observeFavoriteVideosRow(),
                repo.observeFavoriteAlbumsRow()
            ) { videos, albums ->
                listOfNotNull(videos, albums)
            }

            val playlistRows = _playlists.map { playlists ->
                if (playlists.isEmpty()) emptyList()
                else listOf(
                    ContentRow.Playlists(
                        title = "Playlists",
                        items = playlists,
                        sessionId = "home_playlists",
                        sessionTitle = "Playlists",
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

            combine(historyRows, playlistRows, favoriteRows) { history, playlists, favorites ->
                // Recently played rows must remain ahead of Playlists; Playlists
                // belongs immediately before the Favorites section.
                mainRows + history + playlists + favorites
            }.collect { rows ->
                _rows.value = rows
            }
        }
    }
}
