package ir.hamrahang.tv.ui.home

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.home.HomeRepository
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

    val rows: StateFlow<List<ContentRow>>
        get() = _rows

    init {
        loadRows()
        viewModelScope.launch {
            runCatching { playlistRepository.refreshAndSync() }
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

            val playlistRows = playlistRepository.observePlaylists().map { playlists ->
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
                val musicVideosIndex = mainRows.indexOfLast { it is ContentRow.MusicVideos }
                val orderedMainRows = if (musicVideosIndex >= 0) {
                    mainRows.take(musicVideosIndex + 1) +
                        playlists +
                        mainRows.drop(musicVideosIndex + 1)
                } else {
                    mainRows + playlists
                }
                orderedMainRows + history + favorites
            }.collect { rows ->
                _rows.value = rows
            }
        }
    }
}
