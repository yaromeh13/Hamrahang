package ir.hamrahang.tv.ui

import android.app.Dialog
import android.graphics.Color
import android.graphics.drawable.ColorDrawable
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.ScrollView
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.ui.message.MessageManager
import kotlinx.coroutines.launch

/**
 * Shared More actions used by the fullscreen player, playback screen and item details.
 * The action list is intentionally kept aligned with the existing web/TV menu.
 */
object MoreOptionsPanel {

    fun show(
        fragment: Fragment,
        item: PlaybackItem,
        returnFocus: View? = null
    ) {
        val context = fragment.requireContext()
        val dialog = Dialog(context)
        val content = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(20.dp(context), 18.dp(context), 20.dp(context), 18.dp(context))
            background = context.getDrawable(R.drawable.bg_setting_player)
        }
        val scroll = ScrollView(context).apply {
            isFillViewport = true
            overScrollMode = View.OVER_SCROLL_NEVER
            addView(content)
        }
        populate(fragment, content, item) { dialog.dismiss() }
        dialog.setContentView(scroll)
        dialog.window?.let { window ->
            window.setBackgroundDrawable(ColorDrawable(Color.TRANSPARENT))
            window.setLayout(
                (context.resources.displayMetrics.widthPixels * 0.62f).toInt(),
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
        }
        dialog.setOnDismissListener { returnFocus?.requestFocus() }
        dialog.show()
        dialog.window?.setBackgroundDrawable(ColorDrawable(Color.TRANSPARENT))
        dialog.window?.setLayout(
            (context.resources.displayMetrics.widthPixels * 0.62f).toInt(),
            ViewGroup.LayoutParams.WRAP_CONTENT
        )
        content.post { firstFocusable(content)?.requestFocus() }
    }

    fun populate(
        fragment: Fragment,
        container: LinearLayout,
        item: PlaybackItem,
        close: () -> Unit = {}
    ) {
        container.removeAllViews()
        val context = fragment.requireContext()
        val mediaId = item.id.toLongOrNull()

        fun addAction(label: String, action: () -> Unit) {
            val row = TextView(context).apply {
                text = label
                textSize = 17f
                setTextColor(Color.WHITE)
                gravity = Gravity.CENTER_VERTICAL
                setPadding(18.dp(context), 12.dp(context), 18.dp(context), 12.dp(context))
                minHeight = 54.dp(context)
                isFocusable = true
                isFocusableInTouchMode = true
                isClickable = true
                background = context.getDrawable(R.drawable.bg_player_source_row)
                setOnClickListener { action() }
            }
            container.addView(row, LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            ))
        }

        addAction("Like / Favorite") {
            if (mediaId == null) {
                MessageManager.info("This item cannot be added to favorites")
            } else {
                fragment.viewLifecycleOwner.lifecycleScope.launch {
                    val now = FavoriteManager.toggle(mediaId, item.contentType)
                    MessageManager.info(if (now) "Added to favorites" else "Removed from favorites")
                }
            }
        }

        if (item.contentType != ContentType.ARTIST) {
            addAction("to Queue") {
                MessageManager.info(
                    if (ir.hamrahang.tv.playback.PlaybackManager.session.value?.items?.any {
                        it.id == item.id && it.contentType == item.contentType
                    } == true) "Already in the current queue"
                    else "Adding items to the queue from this menu is not available yet"
                )
            }
            addAction("to Playlist") {
                MessageManager.info("Playlists are not available on TV yet")
            }
        }

        addAction("Report") {
            MessageManager.info("Reporting via TV is not yet available.")
        }
        addAction("Go to item") {
            close()
            UiNavigator.openMediaDetail(item.contentType, item.id)
        }
        addAction("Go to artist") {
            close()
            val artist = item.artists.firstOrNull()
            if (artist == null) MessageManager.info("No artist available")
            else UiNavigator.openArtist(artist)
        }
    }

    private fun firstFocusable(parent: ViewGroup): View? {
        for (index in 0 until parent.childCount) {
            val child = parent.getChildAt(index)
            if (child.isFocusable && child.visibility == View.VISIBLE) return child
            if (child is ViewGroup) firstFocusable(child)?.let { return it }
        }
        return null
    }

    private fun Int.dp(context: android.content.Context): Int =
        (this * context.resources.displayMetrics.density).toInt()
}
