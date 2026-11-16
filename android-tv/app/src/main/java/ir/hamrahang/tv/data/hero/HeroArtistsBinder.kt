package ir.hamrahang.tv.ui.hero

import android.view.LayoutInflater
import android.view.View
import android.widget.LinearLayout
import android.widget.TextView
import com.bumptech.glide.Glide
import com.google.android.material.imageview.ShapeableImageView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.ui.UiNavigator
import ir.hamrahang.tv.util.displayTitle

object HeroArtistsBinder {
    fun bind(
        container: LinearLayout,
        artists: List<Artist>
    ) {
        container.removeAllViews()
        if (artists.isEmpty()) {
            container.visibility = View.GONE
            return
        }

        container.visibility = View.VISIBLE
        val inflater = LayoutInflater.from(container.context)
        artists.forEach { artist ->
            val item = inflater.inflate(
                R.layout.item_artist_chip,
                container,
                false
            )

            val img = item.findViewById<ShapeableImageView>(R.id.imgArtist)
            val txt = item.findViewById<TextView>(R.id.txtArtist)
            txt.text = artist.displayTitle()

            Glide.with(container)
                .load(artist.thumb)
                .circleCrop()
                .into(img)

            item.setOnClickListener {
                UiNavigator.openArtist(artist)
            }

            container.addView(item)
        }
    }
}