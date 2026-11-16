package ir.hamrahang.tv.ui.search

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository

import ir.hamrahang.tv.data.search.SearchRepository

import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.data.search.SearchSection
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class SearchViewModel : ViewModel() {

    private val repository = SearchRepository()
    private val artistRepository = ArtistRepository()
    private val albumRepository = AlbumRepository()
    private val songRepository = SongRepository()
    private val videoRepository = VideoRepository()
    private val podcastRepository = PodcastRepository()


    private val _result = MutableStateFlow<List<SearchSection>>(emptyList())

    val result: StateFlow<List<SearchSection>>
        get() = _result

    fun search(query: String) {

        if (query.isBlank()) {

            _result.value = emptyList()
            return

        }

        viewModelScope.launch {

            try {

                val dto = repository.search(query)

                // Read From Room

                val artists =
                    artistRepository.getByIds(
                        dto.search.artists
                            ?.mapNotNull { it.id }
                            ?: emptyList()
                    )

                val albums =
                    albumRepository.getByIds(
                        dto.search.albums
                            ?.mapNotNull { it.id }
                            ?: emptyList()
                    )

                val songs =
                    songRepository.getByIds(
                        dto.search.songs
                            ?.mapNotNull { it.id }
                            ?: emptyList()
                    )

                val musicVideos =
                    videoRepository.getByIds(
                        dto.search.musicvideos?.mapNotNull { it.id } ?: emptyList()
                    ).filter { it.isMusicVideo }

                val videos =
                    videoRepository.getByIds(
                        dto.search.videos?.mapNotNull { it.id } ?: emptyList()
                    ).filterNot { it.isMusicVideo }

                val podcasts =
                    podcastRepository.getByIds(
                        dto.search.podcasts
                            ?.mapNotNull { it.id }
                            ?: emptyList()
                    )
                //--------------------------------------------------
                // Build Sections
                //--------------------------------------------------

                val sections = buildList {

                    if (artists.isNotEmpty())
                        add(SearchSection.Artists(artists))

                    if (songs.isNotEmpty())
                        add(SearchSection.Songs(songs))

                    if (musicVideos.isNotEmpty())
                        add(SearchSection.MusicVideos(musicVideos))

                    if (albums.isNotEmpty())
                        add(SearchSection.Albums(albums))


                    if (podcasts.isNotEmpty())
                        add(SearchSection.Podcasts(podcasts))

                    if (videos.isNotEmpty())
                        add(SearchSection.Videos(videos))
                }

                _result.value = sections

            }

            catch (e: Exception) {

                e.printStackTrace()

                _result.value = emptyList()

            }

        }

    }
}
