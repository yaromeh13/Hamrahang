package ir.hamrahang.tv.ui.hero

import android.widget.LinearLayout
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaMeta

class HeroMetaBinder(
    private val container: LinearLayout
) : HeroBinderBase() {

    fun bind(meta: MediaMeta?) {

        clear(container)

        if (meta == null)
            return

        addItem(
            container,
            R.drawable.ic_calendar,
            meta.rls
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

        addItem(
            container,
            R.drawable.ic_language,
            meta.lan
        )

        addItem(
            container,
            R.drawable.ic_language,
            meta.gnr?.takeIf { it.isNotEmpty() }?.joinToString(" • ")
        )

    }
    /*private fun formatCount(value: Long): String {

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
    }*/

}

/*package ir.hamrahang.tv.ui.hero

import android.view.View
import android.widget.LinearLayout
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaMeta

object HeroMetaBinder {

    fun bind(

        heroRoot: View,

        meta: MediaMeta?

    ) {

        val layout =
            heroRoot.findViewById<LinearLayout>(R.id.heroMetaRoot)
                ?: return

        layout.removeAllViews()

        if (meta == null)
            return

        meta.rls?.let {

            HeroBinderBase.addItem(

                layout,

                R.drawable.ic_calendar,

                it

            )

        }

        meta.ncp?.let {

            HeroBinderBase.addItem(

                layout,

                R.drawable.ic_play_arrow,

                formatCount(it)

            )

        }

        meta.lik?.let {

            HeroBinderBase.addItem(

                layout,

                R.drawable.ic_favorite,

                formatCount(it)

            )

        }

        meta.lan?.let {

            HeroBinderBase.addItem(

                layout,

                R.drawable.ic_language,

                it

            )

        }

        meta.gnr
            ?.takeIf { it.isNotEmpty() }
            ?.let {

                HeroBinderBase.addItem(

                    layout,

                    R.drawable.ic_language,

                    it.joinToString(" • ")

                )

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

            else ->
                value.toString()

        }

    }

}



/*package ir.hamrahang.tv.ui.hero

import android.view.View
import android.widget.LinearLayout
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaMeta

class HeroMetaBinder(

    private val layout: LinearLayout

) : HeroBinderBase() {

    //private val layout: LinearLayout =

     //   root.findViewById(R.id.layoutMeta)

    fun bind(meta: MediaMeta?) {

        clear(layout)

        if (meta == null)
            return

        //------------------------
        // Release
        //------------------------

        meta.rls?.let {

            addItem(

                layout,

                R.drawable.ic_calendar,

                it

            )

        }

        //------------------------
        // Plays
        //------------------------

        meta.ncp?.let {

            addItem(

                layout,

                R.drawable.ic_play_arrow,

                formatCount(it)

            )

        }

        //------------------------
        // Likes
        //------------------------

        meta.lik?.let {

            addItem(

                layout,

                R.drawable.ic_favorite,

                formatCount(it)

            )

        }

        //------------------------
        // Language
        //------------------------

        meta.lan?.let {

            addItem(

                layout,

                R.drawable.ic_language,

                it

            )

        }

        //------------------------
        // Genre
        //------------------------

        meta.gnr
            ?.takeIf {

                it.isNotEmpty()

            }
            ?.let {

                addItem(

                    layout,

                    R.drawable.ic_language,

                    it.joinToString(" • ")

                )

            }

    }

}

/*
package ir.hamrahang.tv.ui.hero

import android.widget.LinearLayout

class HeroMetaBinder(

    private val layout: LinearLayout

) : HeroBinderBase() {

    fun bind(

        items: List<HeroMetaItem>

    ) {

        clear(layout)

        items.forEach {

            addItem(

                layout,

                it.icon,

                it.value

            )

        }

    }

}

/*
package ir.hamrahang.tv.ui.hero

import android.view.LayoutInflater
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.song.Song

class HeroMetaBinder(
    private val root: View
) {

    private val layoutMeta: LinearLayout =
        root.findViewById(R.id.layoutSongMeta)

    data class MetaItem(
        val icon: Int,
        val value: String
    )

    fun bind(song: Song) {

        layoutMeta.removeAllViews()

        val meta = song.meta ?: return

        val inflater = LayoutInflater.from(root.context)

        val items = ArrayList<MetaItem>()

        //------------------------------------
        // Release Date
        //------------------------------------

        meta.rls
            ?.takeIf { it.isNotBlank() }
            ?.let {

                items += MetaItem(
                    R.drawable.ic_calendar,
                    it
                )

            }

        //------------------------------------
        // Plays
        //------------------------------------

        meta.ncp
            ?.takeIf { it > 0 }
            ?.let {

                items += MetaItem(
                    R.drawable.ic_play_arrow,
                    formatCount(it.toLong())
                )

            }

        //------------------------------------

        items.forEach { item ->

            val view = inflater.inflate(
                R.layout.item_meta_chip,
                layoutMeta,
                false
            )

            view.findViewById<ImageView>(R.id.imgIcon)
                .setImageResource(item.icon)

            view.findViewById<TextView>(R.id.txtValue)
                .text = item.value

            layoutMeta.addView(view)

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

            else ->
                value.toString()

        }
    }
}

 */