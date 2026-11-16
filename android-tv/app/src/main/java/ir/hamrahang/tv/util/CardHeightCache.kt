package ir.hamrahang.tv.util

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.annotation.LayoutRes

object CardHeightCache {
    private val cache = mutableMapOf<Int, Int>()

    fun getHeight(context: Context, @LayoutRes layoutRes: Int, parent: ViewGroup): Int {
        return cache.getOrPut(layoutRes) {
            val sample = LayoutInflater.from(context).inflate(layoutRes, parent, false)
            sample.measure(
                View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED),
                View.MeasureSpec.makeMeasureSpec(0, View.MeasureSpec.UNSPECIFIED)
            )

            val lp = sample.layoutParams as? ViewGroup.MarginLayoutParams
            val verticalMargin = (lp?.topMargin ?: 0) + (lp?.bottomMargin ?: 0)

            sample.measuredHeight + verticalMargin   // حالا margin هم حساب می‌شه
        }
    }
}