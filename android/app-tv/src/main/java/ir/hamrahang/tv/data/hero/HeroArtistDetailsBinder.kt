package ir.hamrahang.tv.ui.hero

import android.widget.LinearLayout
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.artist.Artist

class HeroArtistDetailsBinder(
    private val counts: LinearLayout,
    private val info: LinearLayout,
    private val social: LinearLayout
) : HeroBinderBase() {

    fun bind(artist: Artist) {

        clear(counts, info, social)

        val meta = artist.meta ?: return

        //-------------------------
        // counts
        //-------------------------

        addItem(
            counts,
            R.drawable.ic_video,
            meta.ncv?.takeIf { it != "0" }
        )

        addItem(
            counts,
            R.drawable.ic_song,
            meta.ncs?.takeIf { it != "0" }
        )

        addItem(
            counts,
            R.drawable.ic_album,
            meta.nca?.takeIf { it != "0" }
        )

        //-------------------------
        // info
        //-------------------------

        addItem(
            info,
            R.drawable.ic_calendar,
            meta.bd
        )

        addItem(
            info,
            R.drawable.ic_group,
            meta.flw?.let(::formatFollowers)
        )

        addItem(
            info,
            R.drawable.ic_language,
            meta.nat
        )

        //-------------------------
        // socials
        //-------------------------

        addItem(
            social,
            R.drawable.ic_language,
            meta.ytm
        )

        addItem(
            social,
            R.drawable.ic_language,
            meta.ins
        )

        addItem(
            social,
            R.drawable.ic_language,
            meta.tlg
        )

    }

    private fun formatFollowers(

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