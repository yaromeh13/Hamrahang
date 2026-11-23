package ir.hamrahang.tv.recommendations

import android.app.Notification
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.graphics.BitmapFactory
import android.net.Uri
import android.util.Log
import android.os.Build
import androidx.tvprovider.media.tv.PreviewChannel
import androidx.tvprovider.media.tv.PreviewChannelHelper
import androidx.tvprovider.media.tv.PreviewProgram
import androidx.tvprovider.media.tv.TvContractCompat
import ir.hamrahang.tv.R
import ir.hamrahang.tv.RecommendationEntryActivity
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.song.SongRepository
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

/**
 * Publishes locally available music recommendations to the Android TV launcher.
 * API 26+ uses Preview Channels; API 21-25 uses legacy recommendation notifications.
 */
object RecommendationManager {
    private const val TAG = "TvRecommendations"
    private const val PREFS = "tv_recommendations"
    private const val KEY_CHANNEL_ID = "channel_id"
    private const val KEY_BROWSABLE_REQUESTED = "browsable_requested"
    private const val KEY_PROGRAM_IDS = "program_ids"
    private const val KEY_NOTIFICATION_IDS = "notification_ids"
    private const val CHANNEL_PROVIDER_ID = "hamrahang_preview_v1"
    private const val MAX_PROGRAMS = 12

    suspend fun refresh(context: Context) = withContext(Dispatchers.IO) {
        runCatching {
            val appContext = context.applicationContext
            val history = DatabaseProvider.database()
                .playbackHistoryDao()
                .getLastPlayed(
                    type = ContentType.SONG.value,
                    userId = 0L,
                    limit = 12
                )

            val repository = SongRepository()
            val recent = repository.getByIds(history.map { it.mediaId.toString() })

            val popular = if (AppState.offlineMode) {
                repository.getAll().also {
                }
            } else {
                runCatching { repository.getTopSongs() }
                    .onSuccess { Log.i(TAG, "top songs API returned=${it.size}") }
                    .onFailure { Log.w(TAG, "Top songs request failed; using cached songs", it) }
                    .getOrElse {
                        repository.getAll().also { cached ->
                        }
                    }
            }
            val songs = (recent + popular)
                .distinctBy { it.id }
                .filter { it.id.isNotBlank() }
                .take(MAX_PROGRAMS)

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                publishPreviewChannel(appContext, songs)
            } else {
                publishLegacyRecommendations(appContext, songs)
            }
        }.onFailure {
            Log.e(TAG, "Failed to refresh Android TV recommendations", it)
        }
    }

    private fun publishPreviewChannel(context: Context, songs: List<Song>) {
        val helper = PreviewChannelHelper(context)
        val prefs = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

        var channelId = prefs.getLong(KEY_CHANNEL_ID, -1L)
        val storedChannelExists = channelId > 0L && helper.getPreviewChannel(channelId) != null
        if (!storedChannelExists) {
            val builder = PreviewChannel.Builder()
                .setDisplayName("Hamrahang")
                .setDescription("Music picked from your Hamrahang library")
                .setInternalProviderId(CHANNEL_PROVIDER_ID)
                .setAppLinkIntent(homeIntent(context))

            BitmapFactory.decodeResource(context.resources, R.mipmap.ic_launcher)?.let {
                builder.setLogo(it)
            }

            channelId = helper.publishDefaultChannel(builder.build())
            if (channelId <= 0L) {
                Log.e(TAG, "Preview Channel creation failed; invalid channelId=$channelId")
                return
            }
            prefs.edit().putLong(KEY_CHANNEL_ID, channelId).apply()
        } else {
        }

        if (!prefs.getBoolean(KEY_BROWSABLE_REQUESTED, false)) {
            TvContractCompat.requestChannelBrowsable(context, channelId)
            prefs.edit().putBoolean(KEY_BROWSABLE_REQUESTED, true).apply()
        } else {
        }

        val oldProgramIds = prefs.getStringSet(KEY_PROGRAM_IDS, emptySet())
            .orEmpty()
            .mapNotNull { it.toLongOrNull() }
        oldProgramIds.forEach { id -> runCatching { helper.deletePreviewProgram(id) } }

        val newProgramIds = mutableSetOf<String>()
        songs.forEachIndexed { index, song ->
            val title = song.displayTitle().ifBlank { song.id }
            val artwork = song.thumb
            val programBuilder = PreviewProgram.Builder()
                .setChannelId(channelId)
                .setType(TvContractCompat.PreviewPrograms.TYPE_CLIP)
                .setTitle(title)
                .setDescription("Music on Hamrahang")
                .setWeight(MAX_PROGRAMS - index)
                .setInternalProviderId("song:${song.id}")
                .setIntentUri(Uri.parse(detailIntent(context, song.id).toUri(Intent.URI_INTENT_SCHEME)))

            if (!artwork.isNullOrBlank()) {
                programBuilder.setPosterArtUri(Uri.parse(artwork))
            }

            runCatching {
                val id = helper.publishPreviewProgram(programBuilder.build())
                if (id > 0L) {
                    newProgramIds += id.toString()
                } else {
                    Log.w(TAG, "preview program returned invalid rowId=$id")
                }
            }.onFailure {
                Log.e("TvRecommendations", "Failed to publish song card: " + song.id, it)
            }
        }
        prefs.edit().putStringSet(KEY_PROGRAM_IDS, newProgramIds).apply()
    }

    @Suppress("DEPRECATION")
    private fun publishLegacyRecommendations(context: Context, songs: List<Song>) {
        val manager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        val prefs = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

        prefs.getStringSet(KEY_NOTIFICATION_IDS, emptySet())
            .orEmpty()
            .mapNotNull { it.toIntOrNull() }
            .forEach(manager::cancel)

        val newIds = mutableSetOf<String>()
        songs.take(5).forEach { song ->
            val notificationId = song.id.hashCode()
            val title = song.displayTitle().ifBlank { song.id }
            val contentIntent = PendingIntent.getActivity(
                context,
                notificationId,
                detailIntent(context, song.id),
                PendingIntent.FLAG_UPDATE_CURRENT or pendingIntentImmutableFlag()
            )
            val notification = Notification.Builder(context)
                .setSmallIcon(R.mipmap.ic_launcher)
                .setContentTitle(title)
                .setContentText("Music on Hamrahang")
                .setCategory(Notification.CATEGORY_RECOMMENDATION)
                .setContentIntent(contentIntent)
                .setAutoCancel(true)
                                .build()

            manager.notify(notificationId, notification)
            newIds += notificationId.toString()
        }
        prefs.edit().putStringSet(KEY_NOTIFICATION_IDS, newIds).apply()
    }

    private fun detailIntent(context: Context, songId: String): Intent =
        Intent(context, RecommendationEntryActivity::class.java).apply {
            putExtra(RecommendationEntryActivity.EXTRA_TYPE, ContentType.SONG.name)
            putExtra(RecommendationEntryActivity.EXTRA_ID, songId)
        }

    private fun homeIntent(context: Context): Intent =
        Intent(context, RecommendationEntryActivity::class.java)

    private fun pendingIntentImmutableFlag(): Int =
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) PendingIntent.FLAG_IMMUTABLE else 0
}
