package ir.hamrahang.tv.ui.hero

import android.widget.LinearLayout
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaMeta

class HeroMetaBinder {

    fun bind(container: LinearLayout, meta: MediaMeta?) {
        container.removeAllViews()

        if (meta == null) return

        addItem(container, R.drawable.ic_calendar, meta.rls)

        // Duration is stored in meta.dur as seconds.
        addItem(
            container,
            R.drawable.ic_time,
            meta.dur?.takeIf { it > 0 }?.let(::formatDuration)
        )

        addItem(
            container,
            R.drawable.ic_play_arrow,
            meta.ncp?.let(::formatCount)
        )

        addItem(
            container,
            R.drawable.ic_favorite,
            meta.lik?.let(::formatCount)
        )

        addItem(container, R.drawable.ic_language, meta.lan)

        addItem(
            container,
            R.drawable.ic_language,
            meta.gnr?.takeIf { it.isNotEmpty() }?.joinToString(" • ")
        )
    }

    private fun addItem(
        container: LinearLayout,
        icon: Int,
        value: String?
    ) {
        if (value.isNullOrBlank()) return

        val view = android.view.LayoutInflater.from(container.context)
            .inflate(R.layout.item_meta_chip, container, false)

        view.findViewById<android.widget.ImageView>(R.id.imgIcon)
            .setImageResource(icon)

        view.findViewById<android.widget.TextView>(R.id.txtValue)
            .text = value

        container.addView(view)
    }

    private fun formatDuration(totalSeconds: Long): String {
        val hours = totalSeconds / 3600
        val minutes = (totalSeconds % 3600) / 60
        val seconds = totalSeconds % 60

        return if (hours > 0) {
            "%02d:%02d:%02d".format(hours, minutes, seconds)
        } else {
            "%02d:%02d".format(minutes, seconds)
        }
    }

    private fun formatCount(value: Long): String {
        return when {
            value >= 1_000_000_000L ->
                "%.1fB".format(value / 1_000_000_000f)

            value >= 1_000_000L ->
                "%.1fM".format(value / 1_000_000f)

            value >= 1_000L ->
                "%.1fK".format(value / 1_000f)

            else -> value.toString()
        }
    }
}
