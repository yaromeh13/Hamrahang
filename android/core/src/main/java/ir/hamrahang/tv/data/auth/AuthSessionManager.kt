package ir.hamrahang.tv.data.auth

import android.content.Context
import ir.hamrahang.tv.data.favorite.AccountSyncRepository
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.sync.Mutex

/**
 * Serializes refresh-token rotation so MainActivity and the Profile screen
 * cannot exchange the same refresh token concurrently.
 */
object AuthSessionManager {
    private val mutex = Mutex()
    private var lastAttemptAt = 0L

    suspend fun refreshSession(context: Context, force: Boolean = false): String? {
        mutex.lock()
        try {
            val appContext = context.applicationContext
            val cachedAccessToken = AuthSessionStore.accessToken(appContext)

            if (!AuthSessionStore.isLoggedIn(appContext)) {
                FavoriteManager.setAccessToken(null)
                return null
            }

            val now = System.currentTimeMillis()
            if (!force && lastAttemptAt > 0L && now - lastAttemptAt < 30_000L) {
                FavoriteManager.setAccessToken(cachedAccessToken)
                return cachedAccessToken
            }

            val refreshToken = AuthSessionStore.refreshToken(appContext)
            if (refreshToken.isNullOrBlank()) {
                AuthSessionStore.clear(appContext)
                FavoriteManager.setAccessToken(null)
                lastAttemptAt = now
                return null
            }

            return try {
                val response = ApiClient.api.authAction(
                    body = AuthRequest(
                        a = "refresh_session",
                        refresh_token = refreshToken,
                        did = AuthSessionStore.deviceId(appContext)
                    )
                )

                lastAttemptAt = System.currentTimeMillis()
                if (!response.access_token.isNullOrBlank()) {
                    AuthSessionStore.save(appContext, response)
                    FavoriteManager.setAccessToken(response.access_token)
                    try {
                        AccountSyncRepository(appContext).pullServerData(response.access_token)
                    } catch (cancelled: CancellationException) {
                        throw cancelled
                    } catch (_: Exception) {
                        // Keep the refreshed session if a cache pull fails.
                    }
                    response.access_token
                } else if (response.error == "invalid_session" || response.error == "account_disabled") {
                    AuthSessionStore.clear(appContext)
                    FavoriteManager.setAccessToken(null)
                    null
                } else {
                    FavoriteManager.setAccessToken(cachedAccessToken)
                    cachedAccessToken
                }
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                lastAttemptAt = System.currentTimeMillis()
                // Offline: retain the cached session and local data.
                FavoriteManager.setAccessToken(cachedAccessToken)
                cachedAccessToken
            }
        } finally {
            mutex.unlock()
        }
    }
}
