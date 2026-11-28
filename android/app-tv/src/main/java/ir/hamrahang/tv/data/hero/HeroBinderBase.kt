package ir.hamrahang.tv.ui.hero

import android.view.LayoutInflater
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.annotation.DrawableRes
import ir.hamrahang.tv.R

abstract class HeroBinderBase {
    protected fun clear(container: LinearLayout) {
        container.removeAllViews()
    }
    protected fun addItem(
        container: LinearLayout,
        @DrawableRes icon: Int,
        value: String?
    ) {

        if (value.isNullOrBlank())
            return

        val inflater = LayoutInflater.from(container.context)

        val view =
            inflater.inflate(

                R.layout.item_meta_chip,

                container,

                false

            )

        view.findViewById<ImageView>(R.id.imgIcon)
            .setImageResource(icon)

        view.findViewById<TextView>(R.id.txtValue)
            .text = value

        container.addView(view)

    }

    protected fun clear(
        vararg containers: LinearLayout
    ) {
        containers.forEach { it.removeAllViews() }
    }

    protected fun formatCount(
        value: Long
    ): String {

        return when {

            value >= 1_000_000_000L ->
                "%.1fB".format(value / 1_000_000_000f)

            value >= 1_000_000L ->
                "%.1fM".format(value / 1_000_000f)

            value >= 1_000L ->
                "%.1fK".format(value / 1_000f)

            else ->
                value.toString()

        }

    }

}

