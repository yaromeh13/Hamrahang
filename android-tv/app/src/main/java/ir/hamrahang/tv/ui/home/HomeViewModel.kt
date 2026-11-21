package ir.hamrahang.tv.ui.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.home.HomeRepository
import ir.hamrahang.tv.ui.rows.ContentRow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.launch

class HomeViewModel : ViewModel() {

    private val repo = HomeRepository()

    private val _rows = MutableStateFlow<List<ContentRow>>(emptyList())

    val rows: StateFlow<List<ContentRow>>
        get() = _rows

    init {
        loadRows()
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

            val favoriteRows = combine(
                favoriteMusicAndSongs,
                favoritePodcastsAndArtists,
                favoriteVideosAndAlbums
            ) { first, second, third ->
                first + second + third
            }

            combine(historyRows, favoriteRows) { history, favorites ->
                mainRows + history + favorites
            }.collect { rows ->
                _rows.value = rows
            }
        }
    }
}
