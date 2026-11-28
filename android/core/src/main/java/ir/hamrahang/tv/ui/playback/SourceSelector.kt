/*
package ir.hamrahang.tv.player

import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.settings.QualityLevel
import kotlin.math.abs

object SourceSelector {

    fun select(
        sources: List<MediaSource>,
        type: MediaType,
        preferredQuality: QualityLevel
    ): List<MediaSource> {

        if (sources.isEmpty()) {
            return emptyList()
        }

        val normalSources =
            sources.filterNot {
                it.type.equals(
                    "demo",
                    ignoreCase = true
                )
            }

        // اگر سورس اصلی وجود دارد،
        // Demo فقط برای Hero است و نباید پخش شود.
        val playableSources =
            if (normalSources.isNotEmpty()) {
                normalSources
            } else {
                // اگر هیچ سورس اصلی نبود،
                // Demo تنها گزینه موجود است.
                sources
            }

        if (playableSources.size <= 1) {
            return playableSources
        }

        val targetBit =
            targetBit(
                type = type,
                quality = preferredQuality,
                sources = playableSources
            )

        return playableSources.sortedWith(
            compareBy<MediaSource> {

                val bit = parseBit(it.bit)

                if (bit == null) {
                    Int.MAX_VALUE
                } else {
                    abs(bit - targetBit)
                }

            }.thenByDescending {

                parseBit(it.bit) ?: -1

            }
        )
    }

    private fun targetBit(
        type: MediaType,
        quality: QualityLevel,
        sources: List<MediaSource>
    ): Int {

        val bits =
            sources
                .mapNotNull { parseBit(it.bit) }
                .distinct()
                .sorted()

        if (bits.isEmpty()) {
            return Int.MAX_VALUE
        }

        return when (quality) {

            QualityLevel.LOW ->
                bits.first()

            QualityLevel.MEDIUM ->
                bits[bits.lastIndex / 2]

            QualityLevel.HIGH ->
                bits.last()
        }
    }

    private fun parseBit(
        bit: String?
    ): Int? {

        return bit
            ?.trim()
            ?.filter { it.isDigit() }
            ?.toIntOrNull()
    }
}
/*
package ir.hamrahang.tv.player

import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.settings.PlaybackQualityManager
import ir.hamrahang.tv.settings.QualityLevel
import ir.hamrahang.tv.settings.QualityPreference

object SourceSelector {

    /**
     * نقطه ورود اصلی انتخاب Source.
     *
     * این تابع:
     *
     * 1. Demo را از Main جدا می‌کند.
     * 2. اگر Main وجود داشته باشد، Demo را حذف می‌کند.
     * 3. اگر Main وجود نداشته باشد، Demo را استفاده می‌کند.
     * 4. کیفیت مطلوب را از PlaybackQualityManager می‌گیرد.
     * 5. تمام Sourceها را بر اساس اولویت کیفیت مرتب می‌کند.
     *
     * خروجی فقط یک Source نیست.
     *
     * تمام Sourceهای معتبر برمی‌گردند تا PlayerManager
     * بتواند Fallback فعلی خودش را انجام دهد.
     */
    fun select(
        sources: List<MediaSource>,
        type: MediaType
    ): List<MediaSource> {

        if (sources.isEmpty()) {
            return emptyList()
        }

        val playbackSources =
            filterPlaybackSources(sources)

        if (playbackSources.isEmpty()) {
            return emptyList()
        }

        val preference =
            PlaybackQualityManager.getPreferredQuality(type)

        return sortByPreference(
            sources = playbackSources,
            preference = preference
        )
    }

    /**
     * Main / Demo را مشخص می‌کند.
     *
     * اگر حداقل یک Source اصلی وجود داشته باشد:
     *
     * فقط Sourceهای اصلی استفاده می‌شوند.
     *
     * اگر Source اصلی وجود نداشته باشد:
     *
     * Sourceهای Demo استفاده می‌شوند.
     */
    private fun filterPlaybackSources(
        sources: List<MediaSource>
    ): List<MediaSource> {

        val mainSources =
            sources.filterNot { isDemo(it) }

        return if (mainSources.isNotEmpty()) {

            mainSources

        } else {

            sources.filter { isDemo(it) }
        }
    }

    /**
     * تشخیص Demo.
     *
     * طبق API فعلی:
     *
     * type = "demo"
     */
    private fun isDemo(
        source: MediaSource
    ): Boolean {

        return source.type
            ?.trim()
            ?.equals(
                "demo",
                ignoreCase = true
            ) == true
    }

    /**
     * مرتب‌سازی Sourceها بر اساس کیفیت مطلوب.
     *
     * خروجی همیشه تمام Sourceها را نگه می‌دارد.
     *
     * Source اول:
     * بهترین انتخاب
     *
     * Sourceهای بعدی:
     * Fallback
     */
    private fun sortByPreference(
        sources: List<MediaSource>,
        preference: QualityPreference
    ): List<MediaSource> {

        return when (preference) {

            is QualityPreference.Level -> {

                sortByQualityLevel(
                    sources,
                    preference.level
                )
            }

            is QualityPreference.ExactBit -> {

                sortByExactBit(
                    sources,
                    preference.bit
                )
            }
        }
    }

    /**
     * مرتب‌سازی براساس LOW / MEDIUM / HIGH.
     *
     * کیفیت‌ها از روی Sourceهای واقعی Media محاسبه می‌شوند.
     *
     * LOW:
     * نزدیک‌ترین Source به پایین‌ترین کیفیت
     *
     * MEDIUM:
     * نزدیک‌ترین Source به وسط لیست
     *
     * HIGH:
     * نزدیک‌ترین Source به بالاترین کیفیت
     */
    private fun sortByQualityLevel(
        sources: List<MediaSource>,
        level: QualityLevel
    ): List<MediaSource> {

        val indexed =
            sources.mapIndexed { index, source ->

                SourceWithBit(
                    source = source,
                    originalIndex = index,
                    bit = source.bit?.toIntOrNull()
                )
            }

        val valid =
            indexed
                .filter { it.bit != null }
                .sortedBy { it.bit }

        val invalid =
            indexed
                .filter { it.bit == null }

        if (valid.isEmpty()) {
            return sources
        }

        val targetIndex =
            when (level) {

                QualityLevel.LOW ->
                    0

                QualityLevel.MEDIUM ->
                    (valid.lastIndex / 2.0)
                        .roundToNearestInt()

                QualityLevel.HIGH ->
                    valid.lastIndex
            }

        val targetBit =
            valid[targetIndex].bit!!

        return valid
            .sortedWith(
                compareBy<SourceWithBit> {

                    kotlin.math.abs(
                        it.bit!! - targetBit
                    )

                }.thenByDescending {

                    it.bit

                }.thenBy {

                    it.originalIndex

                }
            )
            .map { it.source }
            .plus(
                invalid.map { it.source }
            )
    }

    /**
     * مرتب‌سازی براساس Bit دقیق انتخاب‌شده در Player.
     *
     * اگر Bit دقیق وجود داشته باشد:
     *
     * همان Bit اول قرار می‌گیرد.
     *
     * سپس نزدیک‌ترین کیفیت‌ها.
     *
     * مثال:
     *
     * Available:
     * 128, 192, 256, 320
     *
     * Selected:
     * 256
     *
     * خروجی:
     *
     * 256
     * 320
     * 192
     * 128
     */
    private fun sortByExactBit(
        sources: List<MediaSource>,
        targetBit: Int
    ): List<MediaSource> {

        val indexed =
            sources.mapIndexed { index, source ->

                SourceWithBit(
                    source = source,
                    originalIndex = index,
                    bit = source.bit?.toIntOrNull()
                )
            }

        return indexed
            .sortedWith(
                compareBy<SourceWithBit> {

                    if (it.bit == null) {
                        Int.MAX_VALUE
                    } else {
                        kotlin.math.abs(
                            it.bit - targetBit
                        )
                    }

                }.thenByDescending {

                    it.bit ?: Int.MIN_VALUE

                }.thenBy {

                    it.originalIndex

                }
            )
            .map {
                it.source
            }
    }

    private data class SourceWithBit(
        val source: MediaSource,
        val originalIndex: Int,
        val bit: Int?
    )

    private fun Double.roundToNearestInt(): Int {
        return kotlin.math.round(this).toInt()
    }
}*/