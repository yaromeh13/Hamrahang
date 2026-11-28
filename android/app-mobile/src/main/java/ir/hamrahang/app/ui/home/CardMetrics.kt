package ir.hamrahang.app.ui.home

import android.content.res.Configuration

/**
 * How many cards fit across the screen. The fraction leaves part of the next card
 * visible so it is clear the row scrolls.
 */
object CardMetrics {

    fun visibleCards(configuration: Configuration, style: CardStyle): Float {
        val landscape = configuration.orientation == Configuration.ORIENTATION_LANDSCAPE
        val tablet = configuration.smallestScreenWidthDp >= 600
        val wide = style == CardStyle.WIDE
        return when {
            landscape && tablet -> if (wide) 3.3f else 6.3f
            landscape -> if (wide) 2.6f else 5.3f
            tablet -> if (wide) 2.3f else 4.3f
            else -> if (wide) 1.2f else 2.3f
        }
    }
}
