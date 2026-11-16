package ir.hamrahang.tv.ui.layout

import android.content.Context
import android.graphics.Rect
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView

class HeroLayoutManager(
    context: Context,
    private val heroHeight: Int,
    private val margin: Int
) : LinearLayoutManager(context) {

    override fun requestChildRectangleOnScreen(
        parent: RecyclerView,
        child: View,
        rect: Rect,
        immediate: Boolean,
        focusedChildVisible: Boolean
    ): Boolean {

        val desiredTop = heroHeight + margin

        val currentTop = getDecoratedTop(child)

        val dy = currentTop - desiredTop

        return if (dy != 0) {

            if (immediate)
                parent.scrollBy(0, dy)
            else
                parent.smoothScrollBy(0, dy)

            true

        } else {

            false
        }
    }
}