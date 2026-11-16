package ir.hamrahang.tv.ui.widget

import android.content.Context
import android.graphics.Rect
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView

class HeroFocusLayoutManager(
    context: Context,
    private val heroHeight: Int,
    private val topSpacing: Int = 24
) : LinearLayoutManager(context, VERTICAL, false) {

    override fun onRequestChildFocus(
        parent: RecyclerView,
        state: RecyclerView.State,
        child: View,
        focused: View?
    ): Boolean {

        val rect = Rect()
        child.getDrawingRect(rect)
        parent.offsetDescendantRectToMyCoords(child, rect)

        val targetTop = heroHeight + topSpacing

        val dy = rect.top - targetTop

        if (dy != 0) {
            parent.scrollBy(0, dy)
        }

        return super.onRequestChildFocus(parent, state, child, focused)
    }
}