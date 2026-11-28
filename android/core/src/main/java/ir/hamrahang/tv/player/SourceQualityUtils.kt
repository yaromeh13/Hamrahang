package ir.hamrahang.tv.player

import ir.hamrahang.tv.data.MediaSource

object SourceQualityUtils {

    /**
     * Sourceهایی که واقعاً برای نمایش کیفیت مناسب هستند.
     *
     * Demo حذف می‌شود مگر اینکه هیچ Source اصلی وجود نداشته باشد.
     */
    fun playableSources(
        sources: List<MediaSource>
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

        return if (normalSources.isNotEmpty()) {
            normalSources
        } else {
            sources
        }
    }

    /**
     * کیفیت‌های قابل نمایش برای Audio
     *
     * مثلاً:
     * 64
     * 128
     * 320
     */
    fun audioQualities(
        sources: List<MediaSource>
    ): List<Int> {

        return playableSources(sources)
            .mapNotNull {
                parseBit(it.bit)
            }
            .distinct()
            .sorted()
    }

    /**
     * کیفیت‌های قابل نمایش برای Video
     *
     * مثلاً:
     * 480
     * 720
     * 1080
     */
    fun videoQualities(
        sources: List<MediaSource>
    ): List<Int> {

        return playableSources(sources)
            .mapNotNull {
                parseBit(it.bit)
            }
            .distinct()
            .sorted()
    }

    fun parseBit(
        bit: String?
    ): Int? {

        return bit
            ?.trim()
            ?.filter { it.isDigit() }
            ?.toIntOrNull()
    }
}