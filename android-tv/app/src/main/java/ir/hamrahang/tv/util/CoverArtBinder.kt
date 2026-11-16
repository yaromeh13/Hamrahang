package ir.hamrahang.tv.util

import android.media.MediaMetadataRetriever
import androidx.collection.LruCache
import ir.hamrahang.tv.domain.song.Song
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

object CoverArtBinder {

    // کش در حافظه: song.id -> مدل قابل بارگذاری در Glide (String url یا ByteArray)
    private val cache = LruCache<String, Any>(50)

    suspend fun resolve(song: Song?): Any? {

        if (song == null) return null

        cache.get(song.id)?.let { return it }

        val thumb = song.thumb

        val result: Any? = if (!thumb.isNullOrBlank()) {
            thumb
        } else {
            val url = song.sources.firstOrNull()?.url

            if (url != null) {
                withContext(Dispatchers.IO) {
                    try {
                        val retriever = MediaMetadataRetriever()
                        retriever.setDataSource(url, HashMap())
                        val bytes = retriever.embeddedPicture
                        retriever.release()
                        bytes
                    } catch (e: Exception) {
                        null
                    }
                }
            } else {
                null
            }
        }

        if (result != null) {
            cache.put(song.id, result)
        }

        return result
    }
}