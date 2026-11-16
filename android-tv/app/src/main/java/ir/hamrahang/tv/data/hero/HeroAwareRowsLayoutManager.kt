package ir.hamrahang.tv.ui.layout

import android.content.Context
import android.graphics.Rect
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView

class HeroAwareRowsLayoutManager(
    context: Context,
    private val heroHeight: Int,
    private val spacing: Int
) : LinearLayoutManager(context, VERTICAL, false) {

    override fun requestChildRectangleOnScreen(
        parent: RecyclerView,
        child: View,
        rect: Rect,
        immediate: Boolean,
        focusedChildVisible: Boolean
    ): Boolean {

        // اگر خود کارت فوکوس گرفته
        val row = findContainingRow(parent, child) ?: return false

        val desiredTop = heroHeight + spacing

        val currentTop = row.top

        val dy = currentTop - desiredTop

        if (dy != 0) {

            if (immediate)
                parent.scrollBy(0, dy)
            else
                parent.smoothScrollBy(0, dy)

            return true
        }

        return false
    }

    private fun findContainingRow(
        recycler: RecyclerView,
        child: View
    ): View? {

        var v: View? = child

        while (v != null && v.parent != recycler) {
            v = v.parent as? View
        }

        return v
    }
}