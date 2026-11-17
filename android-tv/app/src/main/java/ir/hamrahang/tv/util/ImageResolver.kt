package ir.hamrahang.tv.util

import android.graphics.drawable.Drawable
import android.widget.ImageView
import com.bumptech.glide.Glide
import com.bumptech.glide.RequestBuilder
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.artist.ArtistMeta

/**
 * Single source of truth for artwork-key priority and Hamrahang image URLs.
 * This class only resolves URLs; loading remains asynchronous through Glide.
 */
object ImageResolver {

    private const val THUMB_BASE = "https://hamrahang.ir/thumb/"
    private const val ART_BASE = "https://hamrahang.ir/art/"

    private fun clean(value: String?): String? =
        value?.trim()?.takeIf { it.isNotEmpty() }

    fun thumb(value: String?): String =
        clean(value)?.let { THUMB_BASE + it + ".jpg" }.orEmpty()

    fun art(value: String?): String =
        clean(value)?.let { ART_BASE + it + ".jpg" }.orEmpty()

    fun song(meta: MediaMeta?): String = thumb(meta?.tmb)

    fun podcast(meta: MediaMeta?): String = thumb(meta?.tmb)

    fun artist(meta: ArtistMeta?): String = thumb(meta?.tmb)

    fun album(meta: MediaMeta?): String = thumb(meta?.tmb)

    fun playlist(meta: MediaMeta?): String = thumb(meta?.tmb)

    fun musicVideo(meta: MediaMeta?): String =
        thumb(clean(meta?.tmv) ?: meta?.tmb)

    fun video(meta: MediaMeta?): String =
        thumb(clean(meta?.cvr) ?: meta?.tmb)

    /**
     * Hero wide background is intentionally independent from card thumbnails:
     * meta.art first, then meta.tmv, and no other fallback.
     */
    fun hero(meta: MediaMeta?): String? =
        clean(meta?.art)?.let(::thumb)
            ?: clean(meta?.tmv)?.let(::thumb)

    fun hero(meta: ArtistMeta?): String? =
        clean(meta?.art)?.let(::thumb)
            ?: clean(meta?.tmv)?.let(::thumb)

    /**
     * Owner artwork priority: owner.meta.ico -> owner.meta.tmb -> source icon.
     * The source icon is optional because current MediaSource payloads do not
     * declare a source-specific icon field.
     */
    fun owner(owner: SourceOwner?, sourceIcon: String? = null): String? {
        val ico = clean(owner?.meta?.ico)
        if (ico != null) return ownerIconUrl(ico)

        val tmb = clean(owner?.meta?.tmb)
        if (tmb != null) return thumb(tmb)

        val source = clean(sourceIcon) ?: return null
        return if (source.startsWith("https://") || source.startsWith("http://")) {
            source
        } else {
            thumb(source)
        }
    }

    private fun ownerIconUrl(key: String): String {
        if (key.startsWith("https://") || key.startsWith("http://")) return key
        if (key.contains(".")) return THUMB_BASE + key

        // Web convention: the last three characters encode the extension.
        if (key.length > 3) {
            val extension = key.takeLast(3).lowercase()
            val base = key.dropLast(3)
            if (base.isNotEmpty() && extension.all { it.isLetterOrDigit() }) {
                return THUMB_BASE + base + "." + extension
            }
        }
        return THUMB_BASE + key + ".jpg"
    }

    /** Shared non-blocking Glide request with a type-specific placeholder/error. */
    fun request(
        imageView: ImageView,
        url: String?,
        fallbackDrawable: Int
    ): RequestBuilder<Drawable> =
        Glide.with(imageView)
            .load(clean(url))
            .placeholder(fallbackDrawable)
            .error(fallbackDrawable)
            .fallback(fallbackDrawable)
}
