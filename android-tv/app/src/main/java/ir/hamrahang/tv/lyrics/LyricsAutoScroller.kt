package ir.hamrahang.tv.lyrics

import android.util.DisplayMetrics
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.LinearSmoothScroller
import androidx.recyclerview.widget.RecyclerView

/**
 * Smoothly keeps the active lyric line centered without jumping the list.
 */
object LyricsAutoScroller {

    fun centerOn(recyclerView: RecyclerView, position: Int) {
        if (position < 0 || !recyclerView.isAttachedToWindow) return

        val layoutManager = recyclerView.layoutManager as? LinearLayoutManager ?: return
        val scroller = object : LinearSmoothScroller(recyclerView.context) {
            override fun getVerticalSnapPreference(): Int = SNAP_TO_ANY

            override fun calculateDtToFit(
                viewStart: Int,
                viewEnd: Int,
                boxStart: Int,
                boxEnd: Int,
                snapPreference: Int
            ): Int {
                val viewCenter = (viewStart + viewEnd) / 2
                val boxCenter = (boxStart + boxEnd) / 2
                return boxCenter - viewCenter
            }

            override fun calculateSpeedPerPixel(displayMetrics: DisplayMetrics): Float =
                45f / displayMetrics.densityDpi.coerceAtLeast(1)
        }

        scroller.targetPosition = position
        layoutManager.startSmoothScroll(scroller)
    }
}
