package ir.hamrahang.tv.hero.ui

import android.view.View
import android.widget.ImageView
import android.widget.TextView
import com.bumptech.glide.Glide
import ir.hamrahang.tv.hero.HeroState
import ir.hamrahang.tv.hero.HeroStyle

class HeroRenderer(
    private val root: View,
    private val image: ImageView,
    private val title: TextView,
    private val subtitle: TextView
) {

    fun render(state: HeroState) {

        title.text = state.title
        subtitle.text = state.subtitle ?: ""

        when (state.style) {

            HeroStyle.MUSICVIDEO -> {
                showSquare()
                load(state.imageUrl)
            }

            HeroStyle.SONG -> {
                showSongLayout()
                load(state.imageUrl)
            }

            HeroStyle.ARTIST -> {
                showArtistLayout()
                loadCircle(state.imageUrl)
            }

            HeroStyle.ALBUM -> {
                showAlbumLayout()
                load(state.imageUrl)
            }
        }
    }

    private fun load(url: String) {
        Glide.with(root)
            .load(url)
            .centerCrop()
            .into(image)
    }

    private fun loadCircle(url: String) {
        Glide.with(root)
            .load(url)
            .circleCrop()
            .into(image)
    }

    private fun showSquare() {
        image.scaleType = ImageView.ScaleType.CENTER_CROP
    }

    private fun showSongLayout() {
        image.scaleType = ImageView.ScaleType.CENTER_CROP
    }

    private fun showArtistLayout() {
        image.scaleType = ImageView.ScaleType.CENTER_CROP
    }

    private fun showAlbumLayout() {
        image.scaleType = ImageView.ScaleType.CENTER_CROP
    }
}