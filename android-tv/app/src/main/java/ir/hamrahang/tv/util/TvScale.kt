package ir.hamrahang.tv.util

import android.content.Context
import android.content.res.Configuration
import android.content.res.Resources
import android.os.Build
import android.util.DisplayMetrics
import android.view.WindowManager
import kotlin.math.min
import kotlin.math.roundToInt

/**
 * Locks layout to a 960dp-wide canvas (1080p TV). Resizing the window
 * changes density only, so a row always fits the same number of cards.
 */
object TvScale {

    const val DESIGN_WIDTH_DP = 960f

    private val applying = ThreadLocal<Boolean>()

    fun scale(context: Context): Context {
        val resources = context.resources
        val (widthPx, heightPx) = windowSize(context, resources)
        if (widthPx <= 0 || heightPx <= 0) return context

        val config = Configuration(resources.configuration)
        if (!writeConfig(config, widthPx, heightPx)) return context

        val wrapped = context.createConfigurationContext(config)
        applyMetrics(wrapped.resources, widthPx, heightPx)
        return wrapped
    }

    fun apply(context: Context, resources: Resources) {
        if (applying.get() == true) return
        applying.set(true)
        try {
            val (widthPx, heightPx) = windowSize(context, resources)
            if (widthPx <= 0 || heightPx <= 0) return
            val metrics = resources.displayMetrics
            val targetDpi = targetDpi(widthPx)
            if (metrics.densityDpi == targetDpi &&
                resources.configuration.screenWidthDp == DESIGN_WIDTH_DP.roundToInt()
            ) {
                return
            }
            applyMetrics(resources, widthPx, heightPx)
        } finally {
            applying.set(false)
        }
    }

    private fun applyMetrics(resources: Resources, widthPx: Int, heightPx: Int) {
        val density = widthPx / DESIGN_WIDTH_DP
        if (density <= 0f) return
        val config = resources.configuration
        if (!writeConfig(config, widthPx, heightPx)) return
        val metrics = resources.displayMetrics
        metrics.density = density
        metrics.scaledDensity = density * config.fontScale
        metrics.densityDpi = config.densityDpi
        @Suppress("DEPRECATION")
        resources.updateConfiguration(config, metrics)
    }

    private fun writeConfig(config: Configuration, widthPx: Int, heightPx: Int): Boolean {
        val density = widthPx / DESIGN_WIDTH_DP
        if (density <= 0f) return false
        config.densityDpi = targetDpi(widthPx)
        config.screenWidthDp = DESIGN_WIDTH_DP.roundToInt()
        config.screenHeightDp = (heightPx / density).roundToInt().coerceAtLeast(1)
        config.smallestScreenWidthDp = min(config.screenWidthDp, config.screenHeightDp)
        return true
    }

    private fun targetDpi(widthPx: Int): Int {
        return ((widthPx / DESIGN_WIDTH_DP) * 160f).roundToInt().coerceAtLeast(1)
    }

    private fun windowSize(context: Context, resources: Resources): Pair<Int, Int> {
        val wm = context.getSystemService(Context.WINDOW_SERVICE) as? WindowManager
        if (wm != null && applying.get() != true) {
            try {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
                    val bounds = wm.currentWindowMetrics.bounds
                    if (bounds.width() > 0 && bounds.height() > 0) {
                        return bounds.width() to bounds.height()
                    }
                } else {
                    val dm = DisplayMetrics()
                    @Suppress("DEPRECATION")
                    wm.defaultDisplay.getMetrics(dm)
                    if (dm.widthPixels > 0 && dm.heightPixels > 0) {
                        return dm.widthPixels to dm.heightPixels
                    }
                }
            } catch (_: Throwable) {
            }
        }
        val dm = resources.displayMetrics
        return dm.widthPixels to dm.heightPixels
    }
}
