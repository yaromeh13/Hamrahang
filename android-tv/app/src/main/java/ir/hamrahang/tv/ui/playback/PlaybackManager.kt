package ir.hamrahang.tv.playback

import android.content.Context
import android.util.Log
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.playback.PlaybackHistoryRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.podcast.PodcastRepository
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.player.SourceSelector
import ir.hamrahang.tv.settings.PlaybackQualityManager
import ir.hamrahang.tv.util.CoverArtBinder
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.launch

object PlaybackManager {

    // Repositories // =========================================================
    private val songRepository = SongRepository()
    private val videoRepository = VideoRepository()
    private val musicVideoRepository = MusicVideoRepository()
    private val podcastRepository = PodcastRepository()
    private val albumRepository = AlbumRepository()

    private val playbackHistoryRepository =
        PlaybackHistoryRepository(
            DatabaseProvider
                .database()
                .playbackHistoryDao()
        )

    // Coroutine
    // =========================================================
    private val supervisorJob = SupervisorJob()
    private val scope = CoroutineScope( Dispatchers.Main.immediate + supervisorJob )
    private var appContext: Context? = null
    private var playCurrentJob: Job? = null
    private var currentContentType: ContentType? = null

    // Session
    // --------------------------------------------------
    private val _session = MutableStateFlow<PlaybackSession?>(null)
    val session: StateFlow<PlaybackSession?> get() = _session

    // Current Queue Item
    // --------------------------------------------------
    private val _currentItem = MutableStateFlow<PlaybackItem?>(null)
    val currentItem: StateFlow<PlaybackItem?> get() = _currentItem

    private var autoPlayJob: Job? = null

    // Init
    // --------------------------------------------------

    fun init(context: Context) {
        if (appContext != null) return
        appContext = context.applicationContext
        scope.launch {
            _currentItem.collectLatest {
                it ?: return@collectLatest
                playCurrent()
            }
        }

        /*scope.launch {
            _currentItem.collectLatest { item ->
                item ?: return@collectLatest
                playCurrent()
            }
        }*/
        //scope.launch { _currentItem.collectLatest { if (it == null) return@collectLatest playCurrent() } }
        /*autoPlayJob =
            CoroutineScope(
                Dispatchers.Main
            ).launch {
                _currentItem.collectLatest { item ->
                    item ?: return@collectLatest
                    playCurrent()
                }
            }*/
    }

    // SESSION
    // ==================================================
    fun start(newSession: PlaybackSession) {
        if (newSession.items.isEmpty()) {
            Log.e( "PLAYBACK", "Cannot start empty session" )
            return
        }
        if (
            newSession.currentIndex !in
            newSession.items.indices
        ) {
            Log.e( "PLAYBACK", "Invalid currentIndex=${newSession.currentIndex}" )
            return
        }
        _session.value = newSession
        updateCurrentItem()
    }

    // CURRENT ITEM
    // ==================================================

    private fun updateCurrentItem() {

        val session =
            _session.value
                ?: return

        _currentItem.value =
            session.items.getOrNull(
                session.currentIndex
            )
    }

    // PLAY SPECIFIC INDEX
    // ==================================================

    fun play(
        index: Int
    ) {
        val session = _session.value ?: return
        if (index !in session.items.indices) { return }
        if (session.currentIndex == index) { return }
        _session.value = session.copy( currentIndex = index )
        updateCurrentItem()
    }

    fun next() {
        val session = _session.value ?: return
        if (session.items.isEmpty()) { return }
        /*val nextIndex = (
                    session.currentIndex + 1
                    ) % session.items.size
*/
        val nextIndex = if ( session.currentIndex >= session.items.lastIndex ) { 0 } else { session.currentIndex + 1 }
        _session.value = session.copy( currentIndex = nextIndex )
        updateCurrentItem()
    }


    fun previous() {
        val session = _session.value ?: return
        if (session.items.isEmpty()) { return }
        val previousIndex =
            if (session.currentIndex <= 0) {
                session.items.lastIndex
            } else {
                session.currentIndex - 1
            }
        _session.value = session.copy( currentIndex = previousIndex )
        updateCurrentItem()
    }

    // PLAY CURRENT
    // ==================================================

    private suspend fun playCurrent() {
        val context = appContext ?: return
        val session = _session.value ?: return
        val item = _currentItem.value ?: return
        val mediaType = session.mediaType

        // ----------------------------------------------
        // جلوگیری از پخش مجدد همان Media
        // ----------------------------------------------

        val currentMedia = PlayerManager.getCurrentMedia()
        if (
            currentMedia?.id == item.id &&
            currentMedia.type == mediaType &&
            currentContentType == item.contentType
        ) {
            return
        }

        // Media Type
        // ----------------------------------------------
        when (mediaType) {
            MediaType.AUDIO -> {
                if (item.contentType == ContentType.PODCAST) {
                    playPodcast(context = context, item = item)
                } else {
                    playSong(context = context, item = item)
                }
            }
            MediaType.VIDEO -> {
                playVideo(context = context, item = item)
            }
        }
    }

    // PLAY SONG
    private suspend fun playSong(
        context: Context,
        item: PlaybackItem
    ) {
        val fullSong = songRepository.getSong( item.id )

        playbackHistoryRepository.record(
            mediaId = fullSong.id.toLong(),
            type = ContentType.SONG.value,
        )

        // Quality // -----------------------------------------------------
        val preferredQuality = PlaybackQualityManager.getPreferredQuality( MediaType.AUDIO )

        // Sources // -----------------------------------------------------
        val selectedSources = SourceSelector.select( sources = fullSong.sources, type = MediaType.AUDIO, preferredQuality = preferredQuality )

        Log.d( "SOURCE_SELECTOR", "Song=${fullSong.id}" )
        Log.d( "SOURCE_SELECTOR", "Quality=$preferredQuality" )
        Log.d( "SOURCE_SELECTOR", "Sources=${selectedSources.map { "${it.bit}:${it.type}" }}" )

        // Full Player State
        val album = fullSong.parents .firstOrNull() ?.id ?.let { albumRepository.getAlbum( it ) }
        val artists = fullSong.artists

        val cover = CoverArtBinder.resolve( fullSong )
        //val cover = fullSong.thumb

        PlayerState.updatePlaybackInfo {
            copy(
                visible = true,
                mediaId = fullSong.id,
                mediaType = MediaType.AUDIO,
                artwork = cover,
                title = fullSong.displayTitle(),
                song = null,//fullSong,
                album = album,
                artists = fullSong.artists,
                liked = false,
                favorite = false,
                downloaded = false,
                metadata = fullSong.meta,
                lyricsEntries = fullSong.lyrics,
                lyrics = fullSong.lyrics.firstOrNull { it.lng?.equals("fa", true) == true && !it.body.isNullOrBlank() }?.body
                    ?: fullSong.lyrics.firstOrNull { !it.body.isNullOrBlank() }?.body,
                sources = selectedSources,
                selectedSource = selectedSources.firstOrNull()
            )
        }
        BackgroundManager.set(cover)

        // PlayerManager
        // -----------------------------------------------------
        currentContentType = ContentType.SONG
        PlayerManager.play(
            context,
            PlayerMedia(
                id = fullSong.id,
                title = fullSong.displayTitle(),
                artist = fullSong.artists.joinToString( " - " ) { it.displayTitle() },
                artwork = fullSong.thumb,
                //artwork = CoverArtBinder.resolve( fullSong ),
                sources = selectedSources,
                type = MediaType.AUDIO,
                contentType = ContentType.SONG )
        )
    }

    // PLAY PODCAST
    private suspend fun playPodcast(
        context: Context,
        item: PlaybackItem
    ) {
        val podcast = podcastRepository.getPodcast(item.id)

        playbackHistoryRepository.record(
            mediaId = podcast.id.toLong(),
            type = ContentType.PODCAST.value
        )

        val preferredQuality =
            PlaybackQualityManager.getPreferredQuality(MediaType.AUDIO)

        val selectedSources = SourceSelector.select(
            sources = podcast.sources,
            type = MediaType.AUDIO,
            preferredQuality = preferredQuality
        )

        val cover = podcast.thumb

        PlayerState.updatePlaybackInfo {
            copy(
                visible = true,
                mediaId = podcast.id,
                mediaType = MediaType.AUDIO,
                artwork = cover,
                title = podcast.displayTitle(),
                song = null,
                album = null,
                artists = podcast.artists,
                liked = false,
                favorite = false,
                downloaded = false,
                metadata = podcast.meta,
                lyricsEntries = podcast.lyrics,
                lyrics = podcast.lyrics.firstOrNull { it.lng?.equals("fa", true) == true && !it.body.isNullOrBlank() }?.body
                    ?: podcast.lyrics.firstOrNull { !it.body.isNullOrBlank() }?.body,
                sources = selectedSources,
                selectedSource = selectedSources.firstOrNull()
            )
        }

        BackgroundManager.set(cover)

        currentContentType = ContentType.PODCAST
        PlayerManager.play(
            context,
            PlayerMedia(
                id = podcast.id,
                title = podcast.displayTitle(),
                artist = podcast.artists.joinToString(" - ") { it.displayTitle() },
                artwork = cover,
                sources = selectedSources,
                type = MediaType.AUDIO,
                contentType = ContentType.PODCAST
            )
        )
    }

    // PLAY VIDEO / MUSIC VIDEO
    private suspend fun playVideo(
        context: Context,
        item: PlaybackItem
    ) {
        val fullVideo = if (item.contentType == ContentType.MUSICVIDEO) {
            musicVideoRepository.getMusicVideo(item.id)
        } else {
            videoRepository.getVideo(item.id)
        }

        playbackHistoryRepository.record(
            mediaId = fullVideo.id.toLong(),
            type = fullVideo.contentType.value
        )

        val preferredQuality =
            PlaybackQualityManager.getPreferredQuality(MediaType.VIDEO)

        val selectedSources = SourceSelector.select(
            sources = fullVideo.sources,
            type = MediaType.VIDEO,
            preferredQuality = preferredQuality
        )

        Log.d("SOURCE_SELECTOR", "Video=${fullVideo.id}")
        Log.d("SOURCE_SELECTOR", "VideoType=${fullVideo.contentType}")
        Log.d("SOURCE_SELECTOR", "Quality=$preferredQuality")
        Log.d("SOURCE_SELECTOR", "Sources=${selectedSources.map { "${it.bit}:${it.type}" }}")

        val cover = fullVideo.thumb

        PlayerState.updatePlaybackInfo {
            copy(
                visible = true,
                mediaId = fullVideo.id,
                mediaType = MediaType.VIDEO,
                artwork = cover,
                title = fullVideo.displayTitle(),
                song = null,
                album = null,
                artists = fullVideo.artists,
                liked = false,
                favorite = false,
                downloaded = false,
                metadata = fullVideo.meta,
                lyricsEntries = fullVideo.lyrics,
                lyrics = fullVideo.lyrics.firstOrNull { it.lng.equals("fa", true) && !it.body.isNullOrBlank() }?.body
                    ?: fullVideo.lyrics.firstOrNull { !it.body.isNullOrBlank() }?.body,
                sources = selectedSources,
                selectedSource = selectedSources.firstOrNull()
            )
        }

        BackgroundManager.set(cover)

        currentContentType = fullVideo.contentType
        PlayerManager.play(
            context,
            PlayerMedia(
                id = fullVideo.id,
                title = fullVideo.displayTitle(),
                artist = fullVideo.artists.joinToString(" - ") { it.displayTitle() },
                artwork = cover,
                sources = selectedSources,
                type = MediaType.VIDEO,
                contentType = fullVideo.contentType
            )
        )
    }

    // ALBUM
    // ==================================================
    suspend fun startAlbum(
        album: Album
    ) {
        val details = albumRepository.getAlbum( album.id )
        val songs = songRepository.getByIds( details.songs )
        val items = songs.map {
            PlaybackItem(
                id = it.id,
                title = it.displayTitle(),
                artwork = it.thumb,
                artists = it.artists
            )
        }

        start(
            PlaybackSession(
                id = details.id,
                title = details.displayTitle(),
                source = PlaybackSource.ALBUM,
                items = items,
                currentIndex = 0,
                mediaType = MediaType.AUDIO
            )
        )
    }

    // STOP SESSION
    fun stop() {
        playCurrentJob?.cancel()
        _session.value = null
        _currentItem.value = null
    }
}

/*package ir.hamrahang.tv.playback

import android.util.Log
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.player.BackgroundManager
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.player.PlayerMetadata
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.player.SourceSelector
import ir.hamrahang.tv.settings.PlaybackQualityManager
import ir.hamrahang.tv.util.CoverArtBinder
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.launch

object PlaybackManager {
    private val repository = SongRepository()
    private val albumRepository = AlbumRepository()
    //private val userRepository = UserRepository()   // بعدا میسازی
    // Session فعلی
    private val _session = MutableStateFlow<PlaybackSession?>(null)
    val session: StateFlow<PlaybackSession?> get() = _session

    // آهنگ انتخاب شده فعلی
    private val _currentItem  = MutableStateFlow<Song?>(null)
    val currentItem: StateFlow<Song?> get() = _currentItem
    private var appContext: android.content.Context? = null
    private var autoPlayJob: kotlinx.coroutines.Job? = null

    fun init(context: android.content.Context) {

        if (appContext != null) return

        appContext = context.applicationContext

        autoPlayJob = kotlinx.coroutines.CoroutineScope(
            kotlinx.coroutines.Dispatchers.Main
        ).launch {
            _currentItem.collectLatest { song ->
                song ?: return@collectLatest
                playCurrent()
            }
        }
    }
/*
    private suspend fun playCurrent() {

        val ctx = appContext ?: return
        val fullSong = ensureCurrentSong() ?: return

        //val currentId = PlayerState.uiState.value.mediaId
        val currentId = PlayerState.playbackInfo.value.mediaId
        if (currentId == fullSong.id) return

        val selectedSources =
            SourceSelector.select(
                sources = fullSong.sources,
                type = MediaType.AUDIO
            )

        PlayerManager.play(
            ctx,
            PlayerMedia(
                id = fullSong.id,
                title = fullSong.displayTitle(),
                artist = fullSong.artists.joinToString(" - ") { it.displayTitle() },
                artwork = fullSong.thumb,
                sources = selectedSources,
                type = MediaType.AUDIO
            )
        )
    }*/
/*
    private suspend fun playCurrent() {

        val ctx = appContext ?: return
        val song = _currentSong.value ?: return

        val currentMedia = PlayerManager.getCurrentMedia()

        if (currentMedia?.id == song.id) {
            return
        }

        val fullSong = ensureCurrentSong() ?: return

        val quality =
            PlaybackQualityManager.getPreferredQuality(
                MediaType.AUDIO
            )

        val sources =
            SourceSelector.select(
                sources = fullSong.sources,
                preferredQuality = quality
            )

        PlayerManager.play(
            ctx,
            PlayerMedia(
                id = fullSong.id,
                title = fullSong.displayTitle(),
                artist = fullSong.artists.joinToString(" - ") {
                    it.displayTitle()
                },
                artwork = fullSong.thumb,
                sources = sources,
                type = MediaType.AUDIO
            )
        )
    }*/
    private suspend fun playCurrent() {

        val ctx = appContext ?: return
        val song = _currentSong.value ?: return

        val currentMedia = PlayerManager.getCurrentMedia()

        if (currentMedia?.id == song.id) {
            return
        }

        val fullSong = ensureCurrentSong() ?: return

        val preferredQuality =
            PlaybackQualityManager.getPreferredQuality(
                MediaType.AUDIO
            )

        val selectedSources =
            SourceSelector.select(
                sources = fullSong.sources,
                type = MediaType.AUDIO,
                preferredQuality = preferredQuality
            )
    Log.d(
        "SOURCE_SELECTOR",
        "Song=${fullSong.id}"
    )

    Log.d(
        "SOURCE_SELECTOR",
        "Quality=$preferredQuality"
    )

    Log.d(
        "SOURCE_SELECTOR",
        "Sources=${selectedSources.map { "${it.bit}:${it.type}" }}"
    )
        PlayerManager.play(
            ctx,
            PlayerMedia(
                id = fullSong.id,
                title = fullSong.displayTitle(),
                artist = fullSong.artists.joinToString(" - ") {
                    it.displayTitle()
                },
                artwork = fullSong.thumb,
                sources = selectedSources,
                type = MediaType.AUDIO
            )
        )
    }

    fun start(session: PlaybackSession) {
         _session.value = session
        updateCurrentItem()
    }

    private fun updateCurrentItem() {
        val session = _session.value ?: return
        _currentItem.value = session.items.getOrNull(session.currentIndex)
    }

    fun play(index: Int) {
        val session = _session.value ?: return
        if (index !in session.songs.indices) return
        if (session.currentIndex == index) return
        _session.value = session.copy(currentIndex = index)
        updateCurrentSong()
    }

    fun next() {
        val session = _session.value ?: return
        val next = (session.currentIndex + 1) % session.songs.size
        _session.value = session.copy(currentIndex = next)
        updateCurrentSong()
    }

    fun previous() {
        val session = _session.value ?: return
        val previous =
            if (session.currentIndex == 0) session.songs.lastIndex
            else session.currentIndex - 1
        _session.value = session.copy(currentIndex = previous)
        updateCurrentSong()
    }
    fun updateCurrentSong(song: Song) {

        val session = _session.value ?: return
        val list = session.songs.toMutableList()
        val index = session.currentIndex
        if (index !in list.indices) return

        list[index] = song
        _session.value = session.copy(songs = list)
        _currentSong.value = song
    }
    suspend fun ensureCurrentSong(): Song? {

        val song = _currentSong.value ?: return null
        val fullSong = repository.getSong(song.id)
        updateCurrentSong(fullSong)

        //----------------------Album--------------------------
         val album = fullSong.parents.firstOrNull()?.id?.let { albumRepository.getAlbum(it) }

        //-----------------------Artists-------------------------
        val artists = fullSong.artists

        //------------------------------------------------
        // User
        //------------------------------------------------

        //val liked = userRepository.isLiked(fullSong.id)

        //val favorite = userRepository.isFavorite(fullSong.id)

        //val downloaded = userRepository.isDownloaded(fullSong.id)

        //------------------------------------------------
        val cover = CoverArtBinder.resolve(fullSong)
        val metadata = PlayerMetadata(
            release = fullSong.meta?.rls,
            playCount = fullSong.meta?.ncp,
            albumName = album?.displayTitle()
        )
        /*PlayerState.updateMetadata(
            song = fullSong,
            album = album,
            artists = artists,
            liked = false,
            favorite = false,
            downloaded = false
        )*/

        /*PlayerState.update {
            copy(
                visible = true,
                mediaId = fullSong.id,
                artwork = cover,
                title = fullSong.displayTitle(),
                artist = artists.joinToString(" - ") {it.displayTitle()}
            )
        }*/
        PlayerState.updatePlaybackInfo {

            copy(

                visible = true,

                mediaId = fullSong.id,

                mediaType = MediaType.AUDIO,

                artwork = cover,

                title = fullSong.displayTitle(),

                song = fullSong,

                album = album,

                artists = artists,

                liked = false,

                favorite = false,

                downloaded = false,
                metadata = metadata

            )
        }

        //PlayerState.setArtwork(cover)
        BackgroundManager.set(cover)

        return fullSong

    }

    suspend fun startAlbum(album: Album) {

        val details = albumRepository.getAlbum(album.id)
        val songs = repository.getByIds(album.songs)

        start(
            PlaybackSession(
                id = details.id,
                title = details.displayTitle(),
                source = PlaybackSource.ALBUM,
                songs = songs,
                currentIndex = 0
            )
        )
    }
}
*/



