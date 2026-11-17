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
import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.R

private const val PAYLOAD_SELECTION = "payload_selection"

class PlaybackQueueAdapter(
    private val onClick: (Int) -> Unit
) : ListAdapter<PlaybackItem, PlaybackQueueAdapter.VH>(DIFF_CALLBACK) {
    private var currentIndex = 0
    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.PlsCover)

        init {
            itemView.setOnClickListener {
                val position = bindingAdapterPosition
                if (position != RecyclerView.NO_POSITION) {
                    onClick(position)
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

        val fallback = when (item.contentType) {
            ir.hamrahang.tv.data.local.ContentType.VIDEO,
            ir.hamrahang.tv.data.local.ContentType.MUSICVIDEO -> R.drawable.ic_video
            ir.hamrahang.tv.data.local.ContentType.ARTIST -> R.drawable.ic_profile
            ir.hamrahang.tv.data.local.ContentType.ALBUM -> R.drawable.ic_album_disk
            ir.hamrahang.tv.data.local.ContentType.PLAYLIST -> R.drawable.ic_playlist
            else -> R.drawable.ic_song
        }

        Glide.with(holder.itemView)
            .load(item.artwork)
            .transform(
                CenterCrop(),
                RoundedCorners(8)
            )
            .placeholder(fallback)
            .error(fallback)
            .fallback(fallback)
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
            holder.itemView.isSelected = position == currentIndex
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