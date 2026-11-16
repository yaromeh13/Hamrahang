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

    init { loadRows() }

    private fun loadRows() {

        viewModelScope.launch {

            // =========================================
            // Main Home Rows
            // =========================================

            val mainRows =
                buildList {
                    repo.buildMusicVideoRow()?.let(::add)
                    repo.buildVideoRow()?.let(::add)
                    repo.buildTopSongsRow()?.let(::add)
                    repo.buildArtistRow()?.let(::add)
                    //repo.buildTrendingRow()?.let(::add)
                }

            // Playback History
            // =========================================

            combine(

                repo.observePlaybackHistorySongsRow(),

                repo.observePlaybackHistoryMusicVideosRow(),

                repo.observePlaybackHistoryVideosRow()

            ) { historySongs, historyMusicVideos, historyVideos ->

                buildList {

                    // اول Rowهای اصلی
                    addAll(mainRows)

                    // بعد History Songs
                    historySongs?.let(::add)

                    // بعد History Music Videos
                    historyMusicVideos?.let(::add)
                    historyVideos?.let(::add)

                }

            }.collect { rows ->

                _rows.value = rows

            }

        }

    }

}

/*package ir.hamrahang.tv.ui.home

import androidx.lifecycle.ViewModel
import ir.hamrahang.tv.data.home.HomeRepository
import ir.hamrahang.tv.ui.rows.ContentRow
import ir.hamrahang.tv.util.safeLaunch
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

class HomeViewModel : ViewModel() {

    private val repo  = HomeRepository()

    private val _rows =
        MutableStateFlow<List<ContentRow>>(emptyList())

    val rows: StateFlow<List<ContentRow>>
        get() = _rows

    init {
        loadRows()
    }

    private fun loadRows() {

        safeLaunch {

            val rows = mutableListOf<ContentRow>()
            repo.buildMusicVideoRow()?.let(rows::add)
            repo.buildTopSongsRow()?.let(rows::add)
            repo.buildArtistRow()?.let(rows::add)
            //repo.buildTrendingRow()?.let(rows::add)

            // Playback History
            repo.buildPlaybackHistorySongsRow()?.let(rows::add)
            repo.buildPlaybackHistoryMusicVideosRow()?.let(rows::add)

            _rows.value = rows

        }
    }
}
*/