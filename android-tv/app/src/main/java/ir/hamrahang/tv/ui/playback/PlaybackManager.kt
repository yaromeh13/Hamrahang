package ir.hamrahang.tv.playback

import android.content.Context
import android.util.Log
import androidx.media3.common.Player
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
import ir.hamrahang.tv.player.SourceQualityUtils
import ir.hamrahang.tv.settings.PlaybackQualityManager
import ir.hamrahang.tv.settings.SettingsManager
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

    // Queue navigation state (mirrors web SN.player)
    private val playedInShuffle = mutableSetOf<String>()
    private val shuffleHistory = mutableListOf<String>()
    private var shuffleHistoryIndex = -1

    // Init
    // --------------------------------------------------

    fun init(context: Context) {
        if (appContext != null) return
        appContext = context.applicationContext
        PlayerState.setRepeatMode(
            when (SettingsManager.getRepeatMode()) {
                "one" -> Player.REPEAT_MODE_ONE
                "all" -> Player.REPEAT_MODE_ALL
                else -> Player.REPEAT_MODE_OFF
            }
        )
        PlayerState.setShuffle(false)
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
        resetShuffleMemory()
        _session.value = newSession
        updateCurrentItem()
        if (PlayerManager.isPlaying()) {
            markPlayed(newSession.items[newSession.currentIndex])
        }
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

    /**
     * Apply a queue order selected in the UI without restarting the current media.
     * The current item is tracked by its media identity, not by its old index.
     */
    fun reorder(items: List<PlaybackItem>) {
        val current = _session.value ?: return
        if (items.size != current.items.size) return

        fun key(item: PlaybackItem) = "${item.contentType.value}:${item.id}"
        if (items.map(::key).toSet() != current.items.map(::key).toSet()) return

        val currentKey = current.items.getOrNull(current.currentIndex)?.let(::key)
        val newIndex = items.indexOfFirst { key(it) == currentKey }
        if (newIndex == -1) return

        _session.value = current.copy(items = items.toList(), currentIndex = newIndex)
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

    private fun queueKey(item: PlaybackItem): String =
        "${item.contentType.value}:${item.id}"

    private fun resetShuffleMemory() {
        playedInShuffle.clear()
        shuffleHistory.clear()
        shuffleHistoryIndex = -1
    }

    private fun markPlayed(item: PlaybackItem) {
        if (!PlayerState.uiState.value.shuffle) return

        val key = queueKey(item)
        playedInShuffle.add(key)

        if (shuffleHistoryIndex < -1 || shuffleHistoryIndex >= shuffleHistory.size) {
            shuffleHistoryIndex = shuffleHistory.lastIndex
        }
        if (shuffleHistoryIndex == -1 && shuffleHistory.isNotEmpty()) {
            shuffleHistoryIndex = shuffleHistory.lastIndex
        }

        if (shuffleHistoryIndex >= 0 && shuffleHistory[shuffleHistoryIndex] == key) return

        if (shuffleHistoryIndex > 0 && shuffleHistory[shuffleHistoryIndex - 1] == key) {
            shuffleHistoryIndex--
            return
        }

        if (shuffleHistoryIndex >= 0 &&
            shuffleHistoryIndex + 1 < shuffleHistory.size &&
            shuffleHistory[shuffleHistoryIndex + 1] == key
        ) {
            shuffleHistoryIndex++
            return
        }

        while (shuffleHistory.size > shuffleHistoryIndex + 1) {
            shuffleHistory.removeAt(shuffleHistory.lastIndex)
        }
        shuffleHistory.add(key)
        shuffleHistoryIndex = shuffleHistory.lastIndex
    }

    /** Called by PlayerManager only after ExoPlayer actually starts this queue item. */
    fun onPlaybackStarted() {
        val current = _session.value ?: return
        current.items.getOrNull(current.currentIndex)?.let(::markPlayed)
    }

    fun toggleShuffle() {
        val enabled = !PlayerState.uiState.value.shuffle
        PlayerState.setShuffle(enabled)
        resetShuffleMemory()
        if (enabled && PlayerManager.isPlaying()) {
            _session.value?.let { session ->
                session.items.getOrNull(session.currentIndex)?.let(::markPlayed)
            }
        }
    }

    fun toggleRepeat() {
        val nextMode = when (PlayerState.uiState.value.repeatMode) {
            Player.REPEAT_MODE_OFF -> Player.REPEAT_MODE_ONE
            Player.REPEAT_MODE_ONE -> Player.REPEAT_MODE_ALL
            else -> Player.REPEAT_MODE_OFF
        }
        PlayerState.setRepeatMode(nextMode)
        SettingsManager.saveRepeatMode(
            when (nextMode) {
                Player.REPEAT_MODE_ONE -> "one"
                Player.REPEAT_MODE_ALL -> "all"
                else -> "off"
            }
        )
    }

    private fun findQueueIndex(key: String): Int {
        val items = _session.value?.items ?: return -1
        return items.indexOfFirst { queueKey(it) == key }
    }

    private fun nextQueueIndex(session: PlaybackSession, fromError: Boolean): Int {
        val current = session.currentIndex
        val repeatMode = PlayerState.uiState.value.repeatMode
        if (!PlayerState.uiState.value.shuffle) {
            val next = current + 1
            return if (next < session.items.size) next
            else if (repeatMode == Player.REPEAT_MODE_ALL) 0
            else -1
        }

        // If the user previously went Back, follow the already-heard future first.
        val historyNext = shuffleHistoryIndex + 1
        if (historyNext in shuffleHistory.indices) {
            val index = findQueueIndex(shuffleHistory[historyNext])
            if (index >= 0) return index
        }

        val remaining = session.items.indices.filter { index ->
            (fromError || index != current) &&
                queueKey(session.items[index]) !in playedInShuffle
        }
        if (remaining.isNotEmpty()) return remaining.random()

        if (repeatMode != Player.REPEAT_MODE_ALL) return -1

        resetShuffleMemory()
        val nextCycle = session.items.indices.filter { it != current }
        return nextCycle.randomOrNull() ?: current
    }

    private fun previousQueueIndex(session: PlaybackSession): Int {
        val current = session.currentIndex
        val repeatMode = PlayerState.uiState.value.repeatMode

        if (!PlayerState.uiState.value.shuffle) {
            val previous = current - 1
            return if (previous >= 0) previous
            else if (repeatMode == Player.REPEAT_MODE_ALL) session.items.lastIndex
            else -1
        }

        val currentKey = session.items.getOrNull(current)?.let(::queueKey).orEmpty()
        if (shuffleHistoryIndex in shuffleHistory.indices &&
            shuffleHistory[shuffleHistoryIndex] != currentKey
        ) {
            return findQueueIndex(shuffleHistory[shuffleHistoryIndex])
        }

        val historyPrevious = shuffleHistoryIndex - 1
        if (historyPrevious in shuffleHistory.indices) {
            return findQueueIndex(shuffleHistory[historyPrevious])
        }

        val previous = current - 1
        return if (previous >= 0) previous
        else if (repeatMode == Player.REPEAT_MODE_ALL) session.items.lastIndex
        else -1
    }

    fun next(fromError: Boolean = false) {
        val session = _session.value ?: return
        if (session.items.isEmpty()) return

        // Match web PlayNext(): Repeat One restarts the current item except on source failure.
        if (!fromError && PlayerState.uiState.value.repeatMode == Player.REPEAT_MODE_ONE) {
            PlayerManager.restartCurrent()
            return
        }

        val nextIndex = nextQueueIndex(session, fromError)
        if (nextIndex < 0) return

        if (nextIndex == session.currentIndex) {
            PlayerManager.restartCurrent()
        } else {
            play(nextIndex)
        }
    }

    fun previous() {
        val session = _session.value ?: return
        if (session.items.isEmpty()) return

        val currentItem = session.items.getOrNull(session.currentIndex) ?: return
        val activeMedia = PlayerManager.getCurrentMedia()
        val isPendingLoad = activeMedia?.id != currentItem.id ||
            activeMedia.contentType != currentItem.contentType

        // Match web PlayPrevious(): after 3 seconds restart this item instead of skipping.
        if (!isPendingLoad && PlayerManager.getCurrentPosition() > 3_000L) {
            PlayerManager.restartCurrent()
            return
        }

        val previousIndex = previousQueueIndex(session)
        if (previousIndex >= 0) play(previousIndex)
    }

    /**
     * Remove a queue row without disturbing the current playback unless the
     * removed row is the one currently playing.
     */
    fun remove(index: Int) {
        val current = _session.value ?: return
        if (index !in current.items.indices) return

        val removedItem = current.items[index]
        val removedKey = queueKey(removedItem)
        playedInShuffle.remove(removedKey)
        for (historyIndex in shuffleHistory.lastIndex downTo 0) {
            if (shuffleHistory[historyIndex] == removedKey) {
                shuffleHistory.removeAt(historyIndex)
                if (historyIndex <= shuffleHistoryIndex) shuffleHistoryIndex--
            }
        }
        if (shuffleHistory.isEmpty()) shuffleHistoryIndex = -1
        else if (shuffleHistoryIndex >= shuffleHistory.size) shuffleHistoryIndex = shuffleHistory.lastIndex

        val updatedItems = current.items.toMutableList().apply { removeAt(index) }
        if (updatedItems.isEmpty()) {
            playCurrentJob?.cancel()
            _session.value = null
            _currentItem.value = null
            currentContentType = null
            PlayerManager.stop()
            return
        }

        val wasCurrent = index == current.currentIndex
        val nextIndex = when {
            index < current.currentIndex -> current.currentIndex - 1
            wasCurrent -> current.currentIndex.coerceAtMost(updatedItems.lastIndex)
            else -> current.currentIndex
        }

        _session.value = current.copy(
            items = updatedItems,
            currentIndex = nextIndex
        )
        if (wasCurrent) updateCurrentItem()
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
                sources = SourceQualityUtils.playableSources(fullSong.sources),
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
                sources = SourceQualityUtils.playableSources(podcast.sources),
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
                lyrics = fullVideo.lyrics.firstOrNull { it.lng?.equals("fa", true) == true && !it.body.isNullOrBlank() }?.body
                    ?: fullVideo.lyrics.firstOrNull { !it.body.isNullOrBlank() }?.body,
                sources = SourceQualityUtils.playableSources(fullVideo.sources),
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



