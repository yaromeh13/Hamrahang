package ir.hamrahang.tv.util

import android.graphics.drawable.Drawable
import android.widget.ImageView
import com.bumptech.glide.Glide
import com.bumptech.glide.RequestBuilder

/**
 * TV-only image loading adapter. URL selection stays in the shared core module;
 * Android View and Glide operations stay in the TV application.
 */
object TvImageLoader {

    fun request(
        imageView: ImageView,
        url: String?,
        fallbackDrawable: Int
    ): RequestBuilder<Drawable> =
        Glide.with(imageView)
            .load(url?.trim()?.takeIf { it.isNotEmpty() })
            .placeholder(fallbackDrawable)
            .error(fallbackDrawable)
            .fallback(fallbackDrawable)
}
