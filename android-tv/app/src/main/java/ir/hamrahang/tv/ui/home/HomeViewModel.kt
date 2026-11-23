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
     * Read the current account's local playlists when Home becomes visible.
     * This also handles login/session changes that do not modify the Room table.
     */
    fun refreshPlaylists() {
        viewModelScope.launch {
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
