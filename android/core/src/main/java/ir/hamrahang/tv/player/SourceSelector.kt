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

            QualityLevel.HIGH ->
                bits.last()

            QualityLevel.MEDIUM -> {

                val min = bits.first()
                val max = bits.last()

                val midpoint =
                    min + ((max - min) / 2)

                bits.minByOrNull {
                    abs(it - midpoint)
                } ?: bits.first()
            }
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

        val originalSources =
            sources.filterNot {
                it.type.equals(
                    "demo",
                    ignoreCase = true
                )
            }

        val demoSources =
            sources.filter {
                it.type.equals(
                    "demo",
                    ignoreCase = true
                )
            }

        val playableSources =
            if (originalSources.isNotEmpty()) {
                originalSources
            } else {
                demoSources
            }

        return playableSources.sortedBy {

            qualityDistance(
                bit = it.bit,
                type = type,
                preferredQuality = preferredQuality
            )
        }
    }

    private fun qualityDistance(
        bit: String?,
        type: MediaType,
        preferredQuality: QualityLevel
    ): Int {

        val bitrate =
            bit?.toIntOrNull()
                ?: return Int.MAX_VALUE

        val target =
            targetQuality(
                type,
                preferredQuality
            )

        return abs(
            bitrate - target
        )
    }

    private fun targetQuality(
        type: MediaType,
        quality: QualityLevel
    ): Int {

        return when (type) {

            MediaType.AUDIO -> {

                when (quality) {

                    QualityLevel.LOW ->
                        128

                    QualityLevel.MEDIUM ->
                        320

                    QualityLevel.HIGH ->
                        320
                }
            }

            MediaType.VIDEO -> {

                when (quality) {

                    QualityLevel.LOW ->
                        480

                    QualityLevel.MEDIUM ->
                        720

                    QualityLevel.HIGH ->
                        1080
                }
            }
        }
    }
}


/*package ir.hamrahang.tv.player

import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.settings.QualityLevel
import kotlin.math.abs

object SourceSelector {

    fun select(
        sources: List<MediaSource>,
        preferredQuality: QualityLevel
    ): List<MediaSource> {

        if (sources.isEmpty()) {
            return emptyList()
        }

        val originalSources =
            sources.filterNot {
                it.type.equals(
                    "demo",
                    ignoreCase = true
                )
            }

        val demoSources =
            sources.filter {
                it.type.equals(
                    "demo",
                    ignoreCase = true
                )
            }

        val playableSources =
            if (originalSources.isNotEmpty()) {
                originalSources
            } else {
                demoSources
            }

        return playableSources
            .sortedBy {
                qualityDistance(
                    bit = it.bit,
                    preferredQuality = preferredQuality
                )
            }
    }

    private fun qualityDistance(
        bit: String?,
        preferredQuality: QualityLevel
    ): Int {

        val bitrate =
            bit?.toIntOrNull()
                ?: return Int.MAX_VALUE

        val target =
            targetBitrate(
                preferredQuality
            )

        return abs(
            bitrate - target
        )
    }

    private fun targetBitrate(
        quality: QualityLevel
    ): Int {

        return when (quality) {

            QualityLevel.LOW ->
                128

            QualityLevel.MEDIUM ->
                320

            QualityLevel.HIGH ->
                1080
        }
    }
}*/