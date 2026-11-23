package ir.hamrahang.tv.data.local

import android.content.Context
import org.json.JSONArray

/**
 * Stores only submitted search terms on this device.
 * It is intentionally independent from the server and content cache.
 */
object SearchHistoryStore {
    private const val PREFS_NAME = "hamrahang_search_history"
    private const val KEY_QUERIES = "queries"
    private const val MAX_ITEMS = 30

    fun add(context: Context, query: String) {
        val value = query.trim()
        if (value.isEmpty()) return

        synchronized(this) {
            val preferences = context.applicationContext
                .getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            val current = runCatching {
                val json = JSONArray(preferences.getString(KEY_QUERIES, "[]"))
                (0 until json.length()).mapNotNull { index ->
                    json.optString(index).takeIf { it.isNotBlank() }
                }
            }.getOrDefault(emptyList())

            val updated = (listOf(value) + current.filterNot { it.equals(value, ignoreCase = true) })
                .take(MAX_ITEMS)
            val json = JSONArray()
            updated.forEach(json::put)
            preferences.edit().putString(KEY_QUERIES, json.toString()).apply()
        }
    }

    fun clear(context: Context) {
        context.applicationContext
            .getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .remove(KEY_QUERIES)
            .apply()
    }
}
