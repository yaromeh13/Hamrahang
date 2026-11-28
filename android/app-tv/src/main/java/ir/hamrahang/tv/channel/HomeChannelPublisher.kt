package ir.hamrahang.tv.channel

import android.content.ContentUris
import android.content.ContentValues
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.content.res.Configuration
import android.graphics.Bitmap
import android.graphics.Canvas
import android.net.Uri
import android.os.SystemClock
import android.util.Log
import androidx.appcompat.content.res.AppCompatResources
import androidx.tvprovider.media.tv.Channel
import androidx.tvprovider.media.tv.ChannelLogoUtils
import androidx.tvprovider.media.tv.PreviewProgram
import androidx.tvprovider.media.tv.TvContractCompat
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.musicvideo.MusicVideoRepository
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.data.video.VideoRepository
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import java.util.Locale

/**
 * Publishes the default Google TV / Android TV home row for this app.
 * The launcher titles that row with the localized app name.
 */
object HomeChannelPublisher {

    private const val TAG = "HomeChannel"
    private const val PREFS = "home_channel"
    private const val KEY_CHANNEL_ID = "channel_id"
    private const val KEY_BROWSABLE_REQUESTED = "browsable_requested"
    private const val INTERNAL_ID = "hamrahang.home"
    private const val REFRESH_INTERVAL_MS = 60_000L

    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)
    private val mutex = Mutex()
    @Volatile
    private var lastAttemptAt = 0L

    fun publish(context: Context) {
        val app = context.applicationContext
        if (!canPublish(app)) return
        val now = SystemClock.elapsedRealtime()
        if (now - lastAttemptAt < REFRESH_INTERVAL_MS) return
        lastAttemptAt = now
        scope.launch {
            val published = mutex.withLock {
                runCatching { publishNow(app) }
                    .onFailure { error -> Log.e(TAG, "Could not publish home channel", error) }
                    .getOrDefault(false)
            }
            if (!published) lastAttemptAt = 0L
        }
    }

    private fun canPublish(context: Context): Boolean {
        if (!context.packageManager.hasSystemFeature(PackageManager.FEATURE_LEANBACK)) return false
        return context.packageManager.resolveContentProvider(
            TvContractCompat.AUTHORITY,
            0
        ) != null
    }

    private suspend fun publishNow(context: Context): Boolean {
        val channelId = ensureChannel(context) ?: return false
        val programs = loadPrograms(context)
        val values = programs.map { it.toContentValues() }.toTypedArray()
        context.contentResolver.delete(
            TvContractCompat.buildPreviewProgramsUriForChannel(channelId),
            null,
            null
        )
        if (values.isNotEmpty()) {
            context.contentResolver.bulkInsert(
                TvContractCompat.PreviewPrograms.CONTENT_URI,
                values
            )
        }
        Log.i(TAG, "Home channel $channelId updated with ${values.size} programs")
        return true
    }

    private fun ensureChannel(context: Context): Long? {
        val named = localized(context)
        val prefs = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
        val displayName = named.getString(R.string.app_name)
        val values = channelValues(context, displayName)
        val existingId = prefs.getLong(KEY_CHANNEL_ID, -1L)
        if (existingId > 0L) {
            val updated = context.contentResolver.update(
                TvContractCompat.buildChannelUri(existingId),
                values,
                null,
                null
            )
            if (updated > 0) {
                storeLogo(context, existingId)
                requestBrowsableOnce(context, prefs, existingId)
                return existingId
            }
        }

        val uri = context.contentResolver.insert(
            TvContractCompat.Channels.CONTENT_URI,
            values
        ) ?: return null
        val channelId = ContentUris.parseId(uri)
        prefs.edit().putLong(KEY_CHANNEL_ID, channelId).apply()
        storeLogo(context, channelId)
        requestBrowsableOnce(context, prefs, channelId)
        return channelId
    }

    private fun requestBrowsableOnce(
        context: Context,
        prefs: android.content.SharedPreferences,
        channelId: Long
    ) {
        if (prefs.getBoolean(KEY_BROWSABLE_REQUESTED, false)) return
        TvContractCompat.requestChannelBrowsable(context, channelId)
        prefs.edit().putBoolean(KEY_BROWSABLE_REQUESTED, true).apply()
    }

    private fun channelValues(context: Context, displayName: String): ContentValues {
        val openApp = Intent(context, HomeChannelActivity::class.java)
        return Channel.Builder()
            .setType(TvContractCompat.Channels.TYPE_PREVIEW)
            .setDisplayName(displayName)
            .setInternalProviderId(INTERNAL_ID)
            .setAppLinkIntent(openApp)
            .build()
            .toContentValues()
    }

    private fun storeLogo(context: Context, channelId: Long) {
        val bitmap = logoBitmap(context) ?: return
        ChannelLogoUtils.storeChannelLogo(context, channelId, bitmap)
    }

    private fun logoBitmap(context: Context): Bitmap? {
        val drawable = AppCompatResources.getDrawable(context, R.mipmap.ic_launcher) ?: return null
        val size = 320
        val bitmap = Bitmap.createBitmap(size, size, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bitmap)
        drawable.setBounds(0, 0, size, size)
        drawable.draw(canvas)
        return bitmap
    }

    private suspend fun loadPrograms(context: Context): List<PreviewProgram> {
        val named = localized(context)
        val channelId = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
            .getLong(KEY_CHANNEL_ID, -1L)
        if (channelId <= 0L) return emptyList()

        val tiles = ArrayList<Tile>()
        runCatching { MusicVideoRepository().getLatestMusicVideos() }
            .getOrDefault(emptyList())
            .take(8)
            .forEach { video ->
                tiles += Tile(
                    id = video.id,
                    type = video.contentType,
                    title = video.title,
                    subtitle = credits(video.artists),
                    poster = video.thumb,
                    wide = true
                )
            }
        runCatching { VideoRepository().getLatestVideos() }
            .getOrDefault(emptyList())
            .take(6)
            .forEach { video ->
                tiles += Tile(
                    id = video.id,
                    type = ContentType.VIDEO,
                    title = video.title,
                    subtitle = credits(video.artists),
                    poster = video.thumb,
                    wide = true
                )
            }
        runCatching { SongRepository().getTopSongs() }
            .getOrDefault(emptyList())
            .take(6)
            .forEach { song ->
                tiles += Tile(
                    id = song.id,
                    type = ContentType.SONG,
                    title = song.title,
                    subtitle = credits(song.artists),
                    poster = song.thumb,
                    wide = false
                )
            }

        if (tiles.isEmpty()) {
            tiles += Tile(
                id = "",
                type = null,
                title = named.getString(R.string.home_channel_open),
                subtitle = named.getString(R.string.app_name),
                poster = "",
                wide = true
            )
        }

        return tiles.mapIndexed { index, tile ->
            val intent = Intent(context, HomeChannelActivity::class.java).apply {
                if (tile.type != null && tile.id.isNotBlank()) {
                    putExtra(HomeChannel.EXTRA_TYPE, tile.type.apiKey)
                    putExtra(HomeChannel.EXTRA_ID, tile.id)
                }
            }
            val poster = tile.poster.takeIf { it.startsWith("http") }
                ?.let(Uri::parse)
                ?: Uri.parse("android.resource://${context.packageName}/${R.mipmap.ic_launcher}")
            PreviewProgram.Builder()
                .setChannelId(channelId)
                .setType(
                    if (tile.wide) {
                        TvContractCompat.PreviewProgramColumns.TYPE_CLIP
                    } else {
                        TvContractCompat.PreviewProgramColumns.TYPE_TRACK
                    }
                )
                .setTitle(tile.title.ifBlank { named.getString(R.string.app_name) })
                .setDescription(tile.subtitle)
                .setPosterArtUri(poster)
                .setPosterArtAspectRatio(
                    if (tile.wide) {
                        TvContractCompat.PreviewPrograms.ASPECT_RATIO_16_9
                    } else {
                        TvContractCompat.PreviewPrograms.ASPECT_RATIO_1_1
                    }
                )
                .setIntent(intent)
                .setWeight(tiles.size - index)
                .setInternalProviderId(
                    tile.type?.let { "${it.apiKey}:${tile.id}" } ?: "open"
                )
                .build()
        }
    }

    private fun credits(artists: List<Artist>): String =
        artists.map { it.title }.filter { it.isNotBlank() }.joinToString(" · ")

    private fun localized(context: Context): Context {
        val language = AppState.language.ifBlank { "fa" }
        val locale = Locale(language)
        val config = Configuration(context.resources.configuration)
        config.setLocale(locale)
        return context.createConfigurationContext(config)
    }

    private data class Tile(
        val id: String,
        val type: ContentType?,
        val title: String,
        val subtitle: String,
        val poster: String,
        val wide: Boolean
    )
}
