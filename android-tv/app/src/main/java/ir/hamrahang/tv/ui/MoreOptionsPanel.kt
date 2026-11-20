package ir.hamrahang.tv.ui

import android.app.Dialog
import android.graphics.Color
import android.graphics.drawable.ColorDrawable
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.ScrollView
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.favorite.FavoriteManager
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.ui.message.MessageManager
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.launch

/**
 * Shared More panel styled like the web More menu and the native Settings cards.
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
            clipChildren = false
            clipToPadding = false
            setPadding(16.dp(context), 12.dp(context), 16.dp(context), 8.dp(context))
            background = context.getDrawable(R.drawable.bg_setting_player)
        }
        val scroll = ScrollView(context).apply {
            isFillViewport = false
            clipChildren = false
            clipToPadding = false
            overScrollMode = View.OVER_SCROLL_NEVER
            addView(content)
        }
        populate(fragment, content, item) { dialog.dismiss() }
        dialog.setContentView(scroll)
        dialog.window?.let { window ->
            window.setBackgroundDrawable(ColorDrawable(Color.TRANSPARENT))
            window.setLayout(
                (context.resources.displayMetrics.widthPixels * 0.85f).toInt(),
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
        }
        dialog.setOnDismissListener { returnFocus?.requestFocus() }
        dialog.show()
        dialog.window?.setBackgroundDrawable(ColorDrawable(Color.TRANSPARENT))
        dialog.window?.setLayout(
            (context.resources.displayMetrics.widthPixels * 0.85f).toInt(),
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
        container.orientation = LinearLayout.VERTICAL
        val context = fragment.requireContext()
        val mediaId = item.id.toLongOrNull()

        val mainContent = LinearLayout(context).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.TOP
            clipChildren = false
            clipToPadding = false
        }
        val infoColumn = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.TOP or Gravity.START
            clipChildren = false
            clipToPadding = false
        }
        val cover = ImageView(context).apply {
            scaleType = ImageView.ScaleType.CENTER_CROP
            contentDescription = item.title
        }
        Glide.with(fragment)
            .load(item.artwork)
            .transform(CenterCrop(), RoundedCorners(12.dp(context)))
            .placeholder(R.drawable.ic_song)
            .error(R.drawable.ic_song)
            .into(cover)
        infoColumn.addView(cover, LinearLayout.LayoutParams(
            220.dp(context), 220.dp(context)
        ))
        infoColumn.addView(TextView(context).apply {
            text = item.title
            textSize = 22f
            setTextColor(Color.WHITE)
            gravity = Gravity.CENTER
            maxLines = 2
            setPadding(4.dp(context), 12.dp(context), 4.dp(context), 0)
        }, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ))
        val artistNames = item.artists.joinToString("  •  ") { it.displayTitle() }
        if (artistNames.isNotBlank()) {
            infoColumn.addView(TextView(context).apply {
                text = artistNames
                textSize = 17f
                setTextColor(0xFFFFB7B7.toInt())
                gravity = Gravity.CENTER
                maxLines = 2
                setPadding(4.dp(context), 6.dp(context), 4.dp(context), 0)
            }, LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
            ))
        }
        item.playCount?.let { count ->
            infoColumn.addView(TextView(context).apply {
                text = "$count Plays"
                textSize = 17f
                setTextColor(0xFFBDBDBD.toInt())
                gravity = Gravity.CENTER
                setPadding(4.dp(context), 10.dp(context), 4.dp(context), 0)
            }, LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
            ))
        }
        mainContent.addView(infoColumn, LinearLayout.LayoutParams(
            220.dp(context), ViewGroup.LayoutParams.WRAP_CONTENT
        ))

        val actionsColumn = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            clipChildren = false
            clipToPadding = false
            setPadding(12.dp(context), 0, 0, 0)
        }
        mainContent.addView(actionsColumn, LinearLayout.LayoutParams(
            0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f
        ))
        container.addView(mainContent, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT
        ))

        var bookmarked = false
        lateinit var bookmarkCard: View
        val favoriteLabel = if (item.contentType == ContentType.ARTIST) "Follow" else "Bookmark"
        val favoriteDoneLabel = if (item.contentType == ContentType.ARTIST) "Following" else "Bookmarked"
        val favoriteIcon = if (item.contentType == ContentType.ARTIST) {
            R.drawable.ic_artist
        } else {
            R.drawable.ic_bookmark
        }
        bookmarkCard = addTopAction(
            context, actionsColumn, favoriteIcon, favoriteLabel
        ) {
            if (mediaId == null) {
                MessageManager.info("This item cannot be bookmarked")
            } else {
                fragment.viewLifecycleOwner.lifecycleScope.launch {
                    bookmarked = FavoriteManager.toggle(mediaId, item.contentType)
                    bookmarkCard.findViewWithTag<TextView>("action_label")?.text =
                        if (bookmarked) favoriteDoneLabel else favoriteLabel
                    MessageManager.info(
                        when {
                            item.contentType == ContentType.ARTIST && bookmarked -> "Artist followed"
                            item.contentType == ContentType.ARTIST -> "Artist unfollowed"
                            bookmarked -> "Added to bookmarks"
                            else -> "Removed from bookmarks"
                        }
                    )
                }
            }
        }
        if (mediaId != null) {
            fragment.viewLifecycleOwner.lifecycleScope.launch {
                bookmarked = FavoriteManager.isFavorite(mediaId, item.contentType)
                bookmarkCard.tag = if (bookmarked) "bookmarked" else "bookmark"
                val label = bookmarkCard.findViewWithTag<TextView>("action_label")
                label?.text = if (bookmarked) favoriteDoneLabel else favoriteLabel
            }
        }

        addTopAction(context, actionsColumn, R.drawable.ic_add_circle, "to Queue") {
            val added = ir.hamrahang.tv.playback.PlaybackManager.addToQueue(item)
            when {
                added -> MessageManager.info("Adding items to the queue")
                ir.hamrahang.tv.playback.PlaybackManager.session.value?.items?.any {
                    it.id == item.id && it.contentType == item.contentType
                } == true -> MessageManager.info("Already in the current queue")
                else -> MessageManager.info("Unable to add this item to the queue")
            }
        }
        addTopAction(context, actionsColumn, R.drawable.ic_add_circle, "to Playlist") {
            MessageManager.info("Playlists are not available on TV yet")
        }
        addTopAction(context, actionsColumn, R.drawable.ic_error, "Report") {
            MessageManager.info("Reporting via TV is not yet available.")
        }

        val destinationLabel = when (item.contentType) {
            ContentType.SONG -> "Go to song"
            ContentType.PODCAST -> "Go to podcast"
            ContentType.ALBUM -> "Go to album"
            ContentType.VIDEO -> "Go to video"
            ContentType.MUSICVIDEO -> "Go to music video"
            ContentType.ARTIST -> "Go to artist"
            ContentType.PLAYLIST -> "Go to playlist"
        }
        addNavigationAction(
            context, actionsColumn,
            mediaIcon(item.contentType),
            destinationLabel
        ) {
            close()
            if (item.contentType == ContentType.ARTIST) {
                val artist = ir.hamrahang.tv.domain.artist.Artist(
                    id = item.id,
                    name = item.title
                )
                if (artist == null) MessageManager.info("No artist available")
                else UiNavigator.runAfterPlayerDismiss { UiNavigator.openArtist(artist) }
            } else {
                UiNavigator.runAfterPlayerDismiss {
                    UiNavigator.openMediaDetail(item.contentType, item.id)
                }
            }
        }

        if (item.contentType != ContentType.ARTIST && item.artists.isNotEmpty()) {
            addNavigationAction(
                context, actionsColumn, R.drawable.ic_artist, "Go to artist"
            ) {
                close()
                UiNavigator.runAfterPlayerDismiss {
                    UiNavigator.openArtist(item.artists.first())
                }
            }
        }
    }

    private fun addTopAction(
        context: android.content.Context,
        container: LinearLayout,
        icon: Int,
        label: String,
        action: () -> Unit
    ): View {
        var row = container.findViewWithTag<LinearLayout>("top_actions")
        if (row == null) {
            row = LinearLayout(context).apply {
                orientation = LinearLayout.HORIZONTAL
                gravity = Gravity.CENTER_VERTICAL
                clipChildren = false
                clipToPadding = false
                tag = "top_actions"
            }
            container.addView(row, LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, 104.dp(context)
            ).apply { bottomMargin = 12.dp(context) })
        }

        val card = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.CENTER
            isFocusable = true
            isFocusableInTouchMode = true
            isClickable = true
            background = context.getDrawable(R.drawable.bg_setting_option)
            setPadding(6.dp(context), 8.dp(context), 6.dp(context), 8.dp(context))
            setOnClickListener { action() }
        }
        val actionIcon = ImageView(context).apply {
            setImageResource(icon)
            imageTintList = android.content.res.ColorStateList.valueOf(Color.WHITE)
            layoutParams = LinearLayout.LayoutParams(44.dp(context), 44.dp(context))
        }
        card.addView(actionIcon)
        card.setOnFocusChangeListener { view, hasFocus ->
            actionIcon.animate()
                .scaleX(if (hasFocus) 1.28f else 1f)
                .scaleY(if (hasFocus) 1.28f else 1f)
                .setDuration(170)
                .start()
        }
        card.addView(TextView(context).apply {
            text = label
            tag = "action_label"
            textSize = 16f
            setTextColor(Color.WHITE)
            gravity = Gravity.CENTER
            maxLines = 1
            setPadding(0, 6.dp(context), 0, 0)
        })
        val params = LinearLayout.LayoutParams(0, 104.dp(context), 1f)
        if (row.childCount > 0) params.marginStart = 6.dp(context)
        row.addView(card, params)
        row.post {
            val gap = 6.dp(context)
            val tileSize = minOf(
                104.dp(context),
                ((row.width - gap * 3) / 4).coerceAtLeast(1)
            )
            for (index in 0 until row.childCount) {
                val child = row.getChildAt(index)
                child.layoutParams = (child.layoutParams as LinearLayout.LayoutParams).apply {
                    width = tileSize
                    height = tileSize
                }
            }
        }
        return card
    }

    private fun addNavigationAction(
        context: android.content.Context,
        container: LinearLayout,
        icon: Int,
        label: String,
        action: () -> Unit
    ) {
        val row = LinearLayout(context).apply {
            orientation = LinearLayout.HORIZONTAL
            gravity = Gravity.CENTER_VERTICAL
            isFocusable = true
            isFocusableInTouchMode = true
            isClickable = true
            background = context.getDrawable(R.drawable.bg_setting_option)
            setPadding(22.dp(context), 10.dp(context), 22.dp(context), 10.dp(context))
            setOnClickListener { action() }
        }
        val navigationIcon = ImageView(context).apply {
            setImageResource(icon)
            imageTintList = android.content.res.ColorStateList.valueOf(Color.WHITE)
            scaleType = ImageView.ScaleType.CENTER_INSIDE
            layoutParams = LinearLayout.LayoutParams(32.dp(context), 32.dp(context))
        }
        row.addView(navigationIcon)
        row.setOnFocusChangeListener { _, hasFocus ->
            navigationIcon.animate()
                .scaleX(if (hasFocus) 1.35f else 1f)
                .scaleY(if (hasFocus) 1.35f else 1f)
                .setDuration(170)
                .start()
        }
        row.addView(TextView(context).apply {
            text = label
            textSize = 19f
            setTextColor(Color.WHITE)
            setPadding(20.dp(context), 0, 0, 0)
        })
        container.addView(row, LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, 64.dp(context)
        ).apply { bottomMargin = 10.dp(context) })
    }

    private fun mediaIcon(type: ContentType): Int = when (type) {
        ContentType.VIDEO, ContentType.MUSICVIDEO -> R.drawable.ic_video
        ContentType.ARTIST -> R.drawable.ic_artist
        ContentType.ALBUM -> R.drawable.ic_album_disk
        ContentType.PLAYLIST -> R.drawable.ic_playlist
        else -> R.drawable.ic_song
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
