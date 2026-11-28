package ir.hamrahang.app.ui.home

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.home.HomeRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.data.playlist.PlaylistRepository
import ir.hamrahang.tv.domain.home.HomeFeedSection
import ir.hamrahang.tv.favorite.FavoriteManager
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.catch
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.flow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.onStart
import kotlinx.coroutines.launch

/** Raw data only; titles are resolved by the UI so they follow the current language. */
data class HomeData(
    val loading: Boolean = true,
    val sections: List<HomeFeedSection> = emptyList(),
    val playlists: List<PlaylistEntity> = emptyList(),
    val favoritePlaylists: List<PlaylistEntity> = emptyList()
) {
    val isEmpty: Boolean
        get() = sections.isEmpty() && playlists.isEmpty() && favoritePlaylists.isEmpty()
}

class HomeViewModel(application: Application) : AndroidViewModel(application) {

    private val repository = HomeRepository()
    private val playlistRepository = PlaylistRepository(application)

    private val _data = MutableStateFlow(HomeData())
    val data = _data.asStateFlow()

    private var job: Job? = null

    init {
        refresh()
    }

    fun refresh() {
        job?.cancel()
        _data.value = HomeData()
        job = viewModelScope.launch {
            launch { runCatching { playlistRepository.refreshFromServer() } }

            // To add, remove or reorder Home sections, edit this list.
            val sources: List<Flow<Loadable>> = listOf(
                remote { repository.buildVideoRow() },
                remote { repository.buildMusicVideoRow() },
                remote { repository.buildTopSongsRow() },
                remote { repository.buildArtistRow() },
                local(repository.observePlaybackHistorySongsRow()),
                local(repository.observePlaybackHistoryMusicVideosRow()),
                local(repository.observePlaybackHistoryVideosRow()),
                local(repository.observeFavoriteMusicVideosRow()),
                local(repository.observeFavoriteSongsRow()),
                local(repository.observeFavoritePodcastsRow()),
                local(repository.observeFavoriteArtistsRow()),
                local(repository.observeFavoriteVideosRow()),
                local(repository.observeFavoriteAlbumsRow())
            )

            val sections = combine(sources) { results -> results.toList() }

            val playlists = playlistRepository.observePlaylists()
                .catch { emit(emptyList()) }

            val favoritePlaylists = FavoriteManager
                .observeFavorites(ContentType.PLAYLIST)
                .map { favorites ->
                    playlistRepository.getFavoritePlaylists(
                        favorites.map { it.mediaId.toString() }
                    )
                }
                .onStart { emit(emptyList()) }
                .catch { emit(emptyList()) }

            combine(sections, playlists, favoritePlaylists) { results, own, favorites ->
                HomeData(
                    loading = results.any { it.pending },
                    sections = results.mapNotNull { it.section },
                    playlists = own,
                    favoritePlaylists = favorites
                )
            }.collect { _data.value = it }
        }
    }

    private class Loadable(val section: HomeFeedSection?, val pending: Boolean = false)

    private fun remote(load: suspend () -> HomeFeedSection?): Flow<Loadable> = flow {
        emit(Loadable(null, pending = true))
        val section = try {
            load()
        } catch (e: CancellationException) {
            throw e
        } catch (e: Exception) {
            null
        }
        emit(Loadable(section))
    }

    private fun local(source: Flow<HomeFeedSection?>): Flow<Loadable> = flow {
        emit(Loadable(null))
        source.collect { emit(Loadable(it)) }
    }.catch { emit(Loadable(null)) }
}
