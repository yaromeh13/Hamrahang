package ir.hamrahang.tv.playback

import android.os.Handler
import android.os.Looper
import android.view.KeyEvent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewConfiguration
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.R

private const val PAYLOAD_SELECTION = "payload_selection"

class PlaybackQueueAdapter(
    private val onClick: (Int) -> Unit,
    private val onRemove: (Int) -> Unit = {},
    private val onMore: (PlaybackItem) -> Unit = {},
    private val onReorderCommit: (List<PlaybackItem>) -> Unit = {},
    private val onReorderStateChanged: (Boolean) -> Unit = {}
) : ListAdapter<PlaybackItem, PlaybackQueueAdapter.VH>(DIFF_CALLBACK) {

    private val handler = Handler(Looper.getMainLooper())
    private var currentIndex = 0
    private var isReordering = false
    private var movingIndex = RecyclerView.NO_POSITION
    private var movingItemKey: String? = null
    private var originalItems: List<PlaybackItem>? = null
    private var workingItems: List<PlaybackItem>? = null
    private var currentItemKey: String? = null
    private var attachedRecyclerView: RecyclerView? = null

    private fun itemKey(item: PlaybackItem): String = "${item.contentType.value}:${item.id}"

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.PlsCover)
        val mediaTypeIcon: ImageView = view.findViewById(R.id.imgQueueMediaType)
        val remove: TextView = view.findViewById(R.id.btnQueueRemove)
        val more: View = view.findViewById(R.id.btnQueueMore)

        private var centerKeyDown = false
        private var longPressStarted = false
        private val longPressRunnable = Runnable {
            val position = bindingAdapterPosition
            if (centerKeyDown && !isReordering && position != RecyclerView.NO_POSITION) {
                longPressStarted = true
                beginReorder(position)
            }
        }

        init {
            itemView.setOnKeyListener { _, keyCode, event -> handleItemKey(keyCode, event) }
            remove.setOnKeyListener { _, keyCode, event -> handleChildKey(keyCode, event, true) }
            more.setOnKeyListener { _, keyCode, event -> handleChildKey(keyCode, event, false) }

            remove.setOnClickListener {
                val position = bindingAdapterPosition
                if (position == RecyclerView.NO_POSITION) return@setOnClickListener
                if (isReordering) {
                    if (position == movingIndex) finishReorder(true, position)
                } else {
                    onRemove(position)
                }
            }
            more.setOnClickListener {
                val position = bindingAdapterPosition
                if (!isReordering && position != RecyclerView.NO_POSITION) onMore(getItem(position))
            }
            itemView.setOnClickListener {
                val position = bindingAdapterPosition
                if (!isReordering && position != RecyclerView.NO_POSITION) onClick(position)
            }
        }

        private fun handleItemKey(keyCode: Int, event: KeyEvent): Boolean {
            if (isReordering) {
                if (event.action != KeyEvent.ACTION_DOWN) return true
                return handleReorderKey(keyCode)
            }
            if (keyCode != KeyEvent.KEYCODE_DPAD_CENTER && keyCode != KeyEvent.KEYCODE_ENTER) return false
            when (event.action) {
                KeyEvent.ACTION_DOWN -> {
                    if (!centerKeyDown) {
                        centerKeyDown = true
                        longPressStarted = false
                        handler.postDelayed(longPressRunnable, ViewConfiguration.getLongPressTimeout().toLong())
                    }
                    return true
                }
                KeyEvent.ACTION_UP -> {
                    if (!centerKeyDown) return false
                    centerKeyDown = false
                    handler.removeCallbacks(longPressRunnable)
                    if (longPressStarted) {
                        longPressStarted = false
                    } else {
                        val position = bindingAdapterPosition
                        if (position != RecyclerView.NO_POSITION) onClick(position)
                    }
                    return true
                }
            }
            return true
        }

        private fun handleChildKey(keyCode: Int, event: KeyEvent, isRemove: Boolean): Boolean {
            if (isReordering) {
                if (event.action != KeyEvent.ACTION_DOWN) return true
                if (isRemove &&
                    (keyCode == KeyEvent.KEYCODE_DPAD_CENTER || keyCode == KeyEvent.KEYCODE_ENTER)
                ) {
                    finishReorder(commit = true, focusPosition = movingIndex)
                    return true
                }
                return handleReorderKey(keyCode)
            }
            if (event.action != KeyEvent.ACTION_DOWN) return false
            return if (isRemove) {
                when (keyCode) {
                    KeyEvent.KEYCODE_DPAD_LEFT -> { itemView.requestFocus(); true }
                    KeyEvent.KEYCODE_DPAD_RIGHT -> { more.requestFocus(); true }
                    else -> false
                }
            } else if (keyCode == KeyEvent.KEYCODE_DPAD_LEFT) {
                remove.requestFocus()
                true
            } else false
        }

        private fun handleReorderKey(keyCode: Int): Boolean = when (keyCode) {
            KeyEvent.KEYCODE_DPAD_UP -> { moveReorderItem(movingIndex, movingIndex - 1); true }
            KeyEvent.KEYCODE_DPAD_DOWN -> { moveReorderItem(movingIndex, movingIndex + 1); true }
            KeyEvent.KEYCODE_BACK, KeyEvent.KEYCODE_ESCAPE -> {
                finishReorder(false, movingIndex)
                true
            }
            else -> true
        }

        fun bindActionState(position: Int) {
            val moving = isReordering && position == movingIndex
            remove.text = if (moving) "✓" else "×"
            remove.contentDescription = if (moving) "Confirm new position" else "Remove from queue"
            remove.visibility = if (isReordering && !moving) View.INVISIBLE else View.VISIBLE
            more.visibility = if (isReordering) View.INVISIBLE else View.VISIBLE
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context).inflate(R.layout.item_playback_song, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val item = getItem(position)
        holder.title.text = item.title
        holder.artist.text = item.artists.joinToString(" - ") { it.displayTitle() }

        val fallback = when (item.contentType) {
            ir.hamrahang.tv.data.local.ContentType.VIDEO,
            ir.hamrahang.tv.data.local.ContentType.MUSICVIDEO -> R.drawable.ic_video
            ir.hamrahang.tv.data.local.ContentType.ARTIST -> R.drawable.ic_profile
            ir.hamrahang.tv.data.local.ContentType.ALBUM -> R.drawable.ic_album_disk
            ir.hamrahang.tv.data.local.ContentType.PLAYLIST -> R.drawable.ic_playlist
            else -> R.drawable.ic_song
        }
        val typeIcon = when (item.contentType) {
            ir.hamrahang.tv.data.local.ContentType.VIDEO,
            ir.hamrahang.tv.data.local.ContentType.MUSICVIDEO -> R.drawable.ic_video
            ir.hamrahang.tv.data.local.ContentType.ARTIST -> R.drawable.ic_profile
            ir.hamrahang.tv.data.local.ContentType.ALBUM -> R.drawable.ic_album_disk
            ir.hamrahang.tv.data.local.ContentType.PLAYLIST -> R.drawable.ic_playlist
            else -> R.drawable.ic_song
        }
        holder.mediaTypeIcon.setImageResource(typeIcon)

        Glide.with(holder.itemView)
            .load(item.artwork)
            .transform(CenterCrop(), RoundedCorners(8))
            .placeholder(fallback)
            .error(fallback)
            .fallback(fallback)
            .into(holder.cover)

        holder.itemView.isSelected = position == currentIndex
        holder.bindActionState(position)
    }

    override fun onBindViewHolder(holder: VH, position: Int, payloads: MutableList<Any>) {
        if (payloads.contains(PAYLOAD_SELECTION)) {
            holder.itemView.isSelected = position == currentIndex
            holder.bindActionState(position)
            return
        }
        super.onBindViewHolder(holder, position, payloads)
    }

    fun submit(list: List<PlaybackItem>, current: Int) {
        val selectedKey = list.getOrNull(current)?.let(::itemKey)
        if (isReordering) {
            currentItemKey = selectedKey
            currentIndex = (workingItems ?: currentList)
                .indexOfFirst { itemKey(it) == selectedKey }.coerceAtLeast(0)
            notifyItemRangeChanged(0, itemCount, PAYLOAD_SELECTION)
            return
        }

        val oldIndex = currentIndex
        currentIndex = current.coerceIn(0, (list.size - 1).coerceAtLeast(0))
        currentItemKey = selectedKey
        submitList(list.toList()) {
            if (oldIndex != currentIndex) {
                if (oldIndex in 0 until itemCount) notifyItemChanged(oldIndex, PAYLOAD_SELECTION)
                if (currentIndex in 0 until itemCount) notifyItemChanged(currentIndex, PAYLOAD_SELECTION)
            }
        }
    }

    private fun beginReorder(position: Int) {
        if (isReordering || position !in currentList.indices) return
        isReordering = true
        onReorderStateChanged(true)
        movingIndex = position
        movingItemKey = itemKey(currentList[position])
        originalItems = currentList.toList()
        workingItems = currentList.toList()
        currentItemKey = currentList.getOrNull(currentIndex)?.let(::itemKey)
        notifyItemRangeChanged(0, itemCount)
        focusActionAt(position)
    }

    private fun moveReorderItem(from: Int, to: Int) {
        val source = workingItems ?: return
        if (!isReordering || from !in source.indices || to !in source.indices || from == to) return
        val updated = source.toMutableList()
        val moved = updated.removeAt(from)
        updated.add(to, moved)
        workingItems = updated
        movingIndex = to
        currentIndex = updated.indexOfFirst { itemKey(it) == currentItemKey }.coerceAtLeast(0)
        submitList(updated) {
            notifyItemRangeChanged(0, itemCount, PAYLOAD_SELECTION)
            focusActionAt(movingIndex)
        }
    }

    fun cancelReorder(): Boolean {
        if (!isReordering) return false
        finishReorder(commit = false, focusPosition = movingIndex)
        return true
    }

    private fun finishReorder(commit: Boolean, focusPosition: Int) {
        if (!isReordering) return
        val finalItems = if (commit) workingItems.orEmpty() else originalItems.orEmpty()
        val movedKey = movingItemKey
        val restoredPosition = finalItems.indexOfFirst { itemKey(it) == movedKey }
        isReordering = false
        onReorderStateChanged(false)
        movingIndex = RecyclerView.NO_POSITION
        movingItemKey = null
        originalItems = null
        workingItems = null
        currentIndex = finalItems.indexOfFirst { itemKey(it) == currentItemKey }.coerceAtLeast(0)

        submitList(finalItems) {
            notifyItemRangeChanged(0, itemCount)
            if (commit) onReorderCommit(finalItems)
            val target = (if (restoredPosition >= 0) restoredPosition else focusPosition)
                .coerceIn(0, (itemCount - 1).coerceAtLeast(0))
            attachedRecyclerView?.post {
                attachedRecyclerView?.scrollToPosition(target)
                attachedRecyclerView?.post {
                    attachedRecyclerView?.findViewHolderForAdapterPosition(target)?.itemView?.requestFocus()
                }
            }
        }
    }

    private fun focusActionAt(position: Int) {
        attachedRecyclerView?.post {
            attachedRecyclerView?.scrollToPosition(position)
            attachedRecyclerView?.post {
                attachedRecyclerView?.findViewHolderForAdapterPosition(position)
                    ?.itemView?.findViewById<View>(R.id.btnQueueRemove)?.requestFocus()
            }
        }
    }

    override fun onAttachedToRecyclerView(recyclerView: RecyclerView) {
        super.onAttachedToRecyclerView(recyclerView)
        attachedRecyclerView = recyclerView
    }

    override fun onDetachedFromRecyclerView(recyclerView: RecyclerView) {
        if (attachedRecyclerView === recyclerView) attachedRecyclerView = null
        super.onDetachedFromRecyclerView(recyclerView)
    }

    companion object {
        private val DIFF_CALLBACK = object : DiffUtil.ItemCallback<PlaybackItem>() {
            override fun areItemsTheSame(oldItem: PlaybackItem, newItem: PlaybackItem): Boolean =
                oldItem.id == newItem.id && oldItem.contentType == newItem.contentType

            override fun areContentsTheSame(oldItem: PlaybackItem, newItem: PlaybackItem): Boolean =
                oldItem == newItem
        }
    }
}



/*
package ir.hamrahang.tv.playback

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.util.displayTitle

private const val PAYLOAD_SELECTION = "payload_selection"

class PlaybackQueueAdapter(
    private val onClick: (Int) -> Unit
) : ListAdapter<PlaybackItem, PlaybackQueueAdapter.VH>(DIFF_CALLBACK) {

    private var currentIndex = 0
    inner class VH(view: View) : RecyclerView.ViewHolder(view) {

        val title: TextView =
            view.findViewById(R.id.songTitle)

        val artist: TextView =
            view.findViewById(R.id.songArtist)

        val cover: ImageView =
            view.findViewById(R.id.PlsCover)

        init {
            itemView.setOnClickListener {

                val pos = bindingAdapterPosition

                if (pos != RecyclerView.NO_POSITION) {
                    onClick(pos)
                }
            }
        }
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): VH {

        val view = LayoutInflater
            .from(parent.context)
            .inflate(
                R.layout.item_playback_song,
                parent,
                false
            )

        return VH(view)
    }

    override fun onBindViewHolder(
        holder: VH,
        position: Int
    ) {

        val item = getItem(position)

        holder.title.text = item.title

        holder.artist.text =
            item.artists.joinToString(" - ") {
                it.displayTitle()
            }

        Glide.with(holder.itemView)
            .load(item.artwork)
            .transform(
                CenterCrop(),
                RoundedCorners(8)
            )
            .into(holder.cover)

        holder.itemView.isSelected =
            position == currentIndex
    }

    override fun onBindViewHolder(
        holder: VH,
        position: Int,
        payloads: MutableList<Any>
    ) {

        if (payloads.contains(PAYLOAD_SELECTION)) {

            holder.itemView.isSelected =
                position == currentIndex

            return
        }

        super.onBindViewHolder(
            holder,
            position,
            payloads
        )
    }

    fun submit(
        list: List<PlaybackItem>,
        current: Int
    ) {

        val oldIndex = currentIndex

        currentIndex = current

        submitList(list) {

            if (oldIndex != currentIndex) {

                if (
                    oldIndex in 0 until itemCount
                ) {
                    notifyItemChanged(
                        oldIndex,
                        PAYLOAD_SELECTION
                    )
                }

                if (
                    currentIndex in 0 until itemCount
                ) {
                    notifyItemChanged(
                        currentIndex,
                        PAYLOAD_SELECTION
                    )
                }
            }
        }
    }

    companion object {

        private val DIFF_CALLBACK =
            object : DiffUtil.ItemCallback<PlaybackItem>() {

                override fun areItemsTheSame(
                    oldItem: PlaybackItem,
                    newItem: PlaybackItem
                ): Boolean {

                    return oldItem.id == newItem.id
                }

                override fun areContentsTheSame(
                    oldItem: PlaybackItem,
                    newItem: PlaybackItem
                ): Boolean {

                    return oldItem == newItem
                }
            }
    }
}

/*package ir.hamrahang.tv.playback

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.util.displayTitle

private const val PAYLOAD_SELECTION = "payload_selection"

class PlaybackQueueAdapter(
    private val onClick: (Int) -> Unit
) : ListAdapter<Song, PlaybackQueueAdapter.VH>(DIFF_CALLBACK) {

    private var currentIndex = 0

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {

        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.PlsCover)

        init {
            itemView.setOnClickListener {
                val pos = bindingAdapterPosition
                if (pos != RecyclerView.NO_POSITION) {
                    onClick(pos)
                }
            }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_playback_song, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {

        val song = getItem(position)

        holder.title.text = song.displayTitle()
        holder.artist.text = song.artists.joinToString(" - ") { it.displayTitle() }

        Glide.with(holder.itemView)
            .load(song.thumb)
            .transform(CenterCrop(), RoundedCorners(8))
            .into(holder.cover)

        holder.itemView.isSelected = position == currentIndex
    }

    override fun onBindViewHolder(holder: VH, position: Int, payloads: MutableList<Any>) {

        if (payloads.contains(PAYLOAD_SELECTION)) {
            holder.itemView.isSelected = position == currentIndex
            return
        }

        super.onBindViewHolder(holder, position, payloads)
    }

    fun submit(list: List<Song>, current: Int) {

        val oldIndex = currentIndex
        currentIndex = current

        submitList(list) {
            if (oldIndex != currentIndex) {
                if (oldIndex in 0 until itemCount) notifyItemChanged(oldIndex, PAYLOAD_SELECTION)
                if (currentIndex in 0 until itemCount) notifyItemChanged(currentIndex, PAYLOAD_SELECTION)
            }
        }
    }

    companion object {
        private val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Song>() {
            override fun areItemsTheSame(oldItem: Song, newItem: Song) =
                oldItem.id == newItem.id

            override fun areContentsTheSame(oldItem: Song, newItem: Song) =
                oldItem == newItem
        }
    }
}
/*
package ir.hamrahang.tv.playback

import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.utils.AppState

class PlaybackQueueAdapter(
    private val onClick: (Int) -> Unit
) : RecyclerView.Adapter<PlaybackQueueAdapter.VH>() {

    private var songs: List<Song> = emptyList()
    private var currentIndex = 0
    inner class VH(view: View) :
        RecyclerView.ViewHolder(view) {

        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.PlsCover)

        init {
            itemView.setOnClickListener {
                val pos = bindingAdapterPosition
                if (pos != RecyclerView.NO_POSITION) {
                    onClick(pos)
                }
            }
        }
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): VH {

        val view =
            LayoutInflater.from(parent.context)
                .inflate(
                    R.layout.item_playback_song,
                    parent,
                    false
                )

        return VH(view)
    }

    override fun onBindViewHolder(
        holder: VH,
        position: Int
    ) {

        val song = songs[position]
        Log.d(
            "QUEUE",
            "bind ${song.title}"
        )
        holder.title.text = song.displayTitle()
        holder.artist.text =
            song.artists
                .joinToString(" - ") {
                    it.displayTitle()
                }
        Glide.with(holder.itemView)
            .load(song.thumb)
            .transform(
                CenterCrop(),
                RoundedCorners(8)
            )
            .into(holder.cover)

        holder.itemView.isSelected =
            position == currentIndex
    }

    override fun getItemCount(): Int =
        songs.size

    fun submit(
        list: List<Song>,
        current: Int
    ) {
        Log.d(
            "QUEUE",
            "submit size=${list.size}"
        )

        songs = list
        currentIndex = current
        notifyDataSetChanged()
    }
}*/