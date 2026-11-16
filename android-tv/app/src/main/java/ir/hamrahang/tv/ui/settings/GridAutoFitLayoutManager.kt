package ir.hamrahang.tv.ui.settings

import android.content.Context
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import kotlin.math.max

class GridAutoFitLayoutManager(

    context: Context,

    private val columnWidth: Int

) : GridLayoutManager(context, 1) {

    override fun onLayoutChildren(
        recycler: RecyclerView.Recycler,
        state: RecyclerView.State
    ) {

        val totalSpace =

            if (orientation == VERTICAL)

                width - paddingLeft - paddingRight

            else

                height - paddingTop - paddingBottom

        val spanCount =

            max(
                1,
                totalSpace / columnWidth
            )

        if (spanCount != this.spanCount) {

            this.spanCount = spanCount

        }

        super.onLayoutChildren(
            recycler,
            state
        )
    }
}