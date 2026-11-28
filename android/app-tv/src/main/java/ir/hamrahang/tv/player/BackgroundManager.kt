package ir.hamrahang.tv.player

import android.view.View
import android.widget.ImageView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.drawable.DrawableTransitionOptions
import ir.hamrahang.tv.R
import jp.wasabeef.glide.transformations.BlurTransformation

object BackgroundManager {

    private var currentSource: Any? = null

    private var bgImageView: ImageView? = null
    private var dimOverlayView: View? = null

    var blurEnabled: Boolean = true
        private set

    fun attachViews(bgImage: ImageView, dim: View) {
        bgImageView = bgImage
        dimOverlayView = dim
        setDim(DEFAULT_DIM_PERCENT)
        render()
    }

    fun detachViews() {
        bgImageView = null
        dimOverlayView = null
    }

    /**
     * منبع تصویر رو ست می‌کنه. می‌تونه String (URL) یا ByteArray (کاور جاسازی‌شده در فایل) باشه.
     * خروجی CoverArtBinder.resolve() مستقیم قابل پاس دادن به این تابعه.
     */
    fun set(source: Any?) {
        if (source == currentSource) return
        currentSource = source
        render()
    }

    fun reset() {
        if (currentSource == null) return
        currentSource = null
        render()
    }

    fun setDim(percent: Int = DEFAULT_DIM_PERCENT) {
        val clamped = percent.coerceIn(0, 100)
        dimOverlayView?.alpha = clamped / 100f
    }

    fun setFull(full: Boolean) {
        bgImageView?.scaleType =
            if (full) ImageView.ScaleType.CENTER_CROP
            else ImageView.ScaleType.FIT_CENTER
    }

    fun setBlur(enabled: Boolean) {
        if (blurEnabled == enabled) return
        blurEnabled = enabled
        render()
    }

    private fun render() {

        val imageView = bgImageView ?: return
        val source = currentSource

        val isEmpty = source == null ||
                (source is String && source.isBlank())

        if (isEmpty) {
            imageView.setImageResource(R.drawable.bg_default)
            return
        }

        val request = Glide.with(imageView)
            .load(source)
            .placeholder(R.drawable.bg_default)
            .error(R.drawable.bg_default)
            .fallback(R.drawable.bg_default)
            .centerCrop()

        if (blurEnabled) {
            request.transform(BlurTransformation(70, 2))
        }

        request
            .transition(DrawableTransitionOptions.withCrossFade(600))
            .into(imageView)
    }

    private const val DEFAULT_DIM_PERCENT = 40
}