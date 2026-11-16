package ir.hamrahang.tv.settings

import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.utils.AppState

object PlaybackQualityManager {

    private val sessionOverrides = mutableMapOf<MediaType, QualityLevel>()

    fun getPreferredQuality(
        type: MediaType
    ): QualityLevel {

        return sessionOverrides[type]
            ?: when (type) {

                MediaType.AUDIO ->
                    AppState.audioQuality

                MediaType.VIDEO ->
                    AppState.videoQuality
            }
    }

    fun setSessionQuality(
        type: MediaType,
        quality: QualityLevel
    ) {

        sessionOverrides[type] = quality
    }

    fun getSessionQuality(
        type: MediaType
    ): QualityLevel? {

        return sessionOverrides[type]
    }

    fun clearSessionQuality(
        type: MediaType
    ) {

        sessionOverrides.remove(type)
    }

    fun clearAllSessionOverrides() {

        sessionOverrides.clear()
    }
}
/*
package ir.hamrahang.tv.settings

import android.content.Context
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.utils.AppState

object PlaybackQualityManager {

    private const val PREF_NAME = "app_settings"

    private const val KEY_AUDIO_QUALITY = "audio_quality"
    private const val KEY_VIDEO_QUALITY = "video_quality"

    private lateinit var context: Context

    private val sessionOverrides =
        mutableMapOf<MediaType, QualityLevel>()

    fun init(context: Context) {

        this.context =
            context.applicationContext
    }

    private val prefs
        get() = context.getSharedPreferences(
            PREF_NAME,
            Context.MODE_PRIVATE
        )

    fun getPreferredQuality(
        type: MediaType
    ): QualityLevel {

        return sessionOverrides[type]
            ?: getPersistentQuality(type)
    }

    fun setSessionQuality(
        type: MediaType,
        quality: QualityLevel
    ) {

        sessionOverrides[type] = quality
    }

    fun clearSessionQuality(
        type: MediaType
    ) {

        sessionOverrides.remove(type)
    }

    fun clearAllSessionOverrides() {

        sessionOverrides.clear()
    }

    fun setPersistentQuality(
        type: MediaType,
        quality: QualityLevel
    ) {

        when (type) {

            MediaType.AUDIO -> {

                prefs.edit()
                    .putString(
                        KEY_AUDIO_QUALITY,
                        quality.name
                    )
                    .apply()

                AppState.audioQuality = quality
            }

            MediaType.VIDEO -> {

                prefs.edit()
                    .putString(
                        KEY_VIDEO_QUALITY,
                        quality.name
                    )
                    .apply()

                AppState.videoQuality = quality
            }
        }
    }

    private fun getPersistentQuality(
        type: MediaType
    ): QualityLevel {

        return when (type) {

            MediaType.AUDIO ->
                AppState.audioQuality

            MediaType.VIDEO ->
                AppState.videoQuality
        }
    }
}

/*package ir.hamrahang.tv.settings

import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.utils.AppState

object PlaybackQualityManager {

    private val sessionOverrides =
        mutableMapOf<MediaType, QualityLevel>()

    fun getPreferredQuality(
        type: MediaType
    ): QualityLevel {

        return sessionOverrides[type]
            ?: when (type) {

                MediaType.AUDIO ->
                    AppState.audioQuality

                MediaType.VIDEO ->
                    AppState.videoQuality
            }
    }

    fun setSessionOverride(
        type: MediaType,
        quality: QualityLevel
    ) {
        sessionOverrides[type] = quality
    }

    fun clearSessionOverride(
        type: MediaType
    ) {
        sessionOverrides.remove(type)
    }

    fun clearAllSessionOverrides() {
        sessionOverrides.clear()
    }

    fun hasSessionOverride(
        type: MediaType
    ): Boolean {
        return sessionOverrides.containsKey(type)
    }
}


/*package ir.hamrahang.tv.settings

import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.utils.AppState

object PlaybackQualityManager {

    /**
     * کیفیتی که کاربر در تنظیمات اصلی اپ انتخاب کرده.
     *
     * این مقدار Persistent است و از AppState خوانده می‌شود.
     */
    fun getPersistentQuality(
        type: MediaType
    ): QualityLevel {
        return when (type) {
            MediaType.AUDIO -> AppState.audioQuality
            MediaType.VIDEO -> AppState.videoQuality
        }
    }

    /**
     * Session Override
     *
     * کلید:
     * MediaType
     *
     * مقدار:
     * Bit واقعی انتخاب‌شده توسط کاربر
     *
     * مثال:
     * AUDIO -> 256
     * VIDEO -> 720
     */
    private val sessionOverrides =
        mutableMapOf<MediaType, Int>()

    /**
     * کیفیت موقت انتخاب‌شده در Session فعلی.
     *
     * اگر null باشد یعنی کاربر Override موقت ندارد.
     */
    fun getSessionQuality(
        type: MediaType
    ): Int? {
        return sessionOverrides[type]
    }

    /**
     * کیفیت مطلوب فعلی را مشخص می‌کند.
     *
     * اولویت:
     *
     * 1. Session Override
     * 2. Persistent Setting
     */
    fun getPreferredQuality(
        type: MediaType
    ): QualityPreference {

        val sessionBit =
            sessionOverrides[type]

        return if (sessionBit != null) {

            QualityPreference.ExactBit(
                bit = sessionBit
            )

        } else {

            QualityPreference.Level(
                level = getPersistentQuality(type)
            )
        }
    }

    /**
     * کاربر از داخل Player یک Bit واقعی انتخاب کرده.
     *
     * این مقدار فقط در حافظه نگهداری می‌شود.
     *
     * Settings اپ تغییر نمی‌کند.
     */
    fun setSessionQuality(
        type: MediaType,
        bit: Int
    ) {
        sessionOverrides[type] = bit
    }

    /**
     * حذف Override موقت یک نوع Media.
     *
     * بعد از حذف، دوباره Persistent Setting استفاده می‌شود.
     */
    fun clearSessionQuality(
        type: MediaType
    ) {
        sessionOverrides.remove(type)
    }

    /**
     * پاک کردن تمام Overrideهای Session.
     *
     * PlayerManager.release() یا پایان کامل Session اپ
     * می‌تواند در صورت نیاز آن را صدا بزند.
     */
    fun clearAllSessionQualities() {
        sessionOverrides.clear()
    }

    /**
     * کیفیت‌هایی که واقعاً در Sourceهای Media وجود دارند.
     *
     * Duplicateها حذف می‌شوند.
     *
     * مثال:
     *
     * 128
     * 192
     * 256
     * 256
     * 320
     *
     * خروجی:
     *
     * 128
     * 192
     * 256
     * 320
     */
    fun getAvailableQualities(
        sources: List<ir.hamrahang.tv.data.MediaSource>
    ): List<Int> {

        return sources
            .mapNotNull { source ->
                source.bit
                    ?.trim()
                    ?.toIntOrNull()
            }
            .distinct()
            .sorted()
    }
}

/**
 * کیفیت مطلوب برای SourceSelector.
 */
sealed class QualityPreference {

    /**
     * کیفیت کلی تنظیم‌شده در Settings.
     *
     * LOW / MEDIUM / HIGH
     */
    data class Level(
        val level: QualityLevel
    ) : QualityPreference()

    /**
     * کیفیت دقیق انتخاب‌شده در Player.
     *
     * مثال:
     * 256
     * 720
     * 1080
     */
    data class ExactBit(
        val bit: Int
    ) : QualityPreference()
}*/