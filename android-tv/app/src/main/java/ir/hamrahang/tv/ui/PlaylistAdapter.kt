package ir.hamrahang.tv.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.entity.PlaylistEntity
import ir.hamrahang.tv.util.ImageResolver
import ir.hamrahang.tv.util.displayTitle

class PlaylistAdapter(
    private var items: List<PlaylistEntity>,
    private val onFocus: (PlaylistEntity) -> Unit,
    private val onClick: (PlaylistEntity) -> Unit
) : RecyclerView.Adapter<PlaylistAdapter.VH>() {

    companion object {
        const val ITEM_LAYOUT_RES = R.layout.item_content
    }

    class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.itemTitle)
        val subtitle: TextView = view.findViewById(R.id.itemSubtitle)
        val cover: ImageView = view.findViewById(R.id.itemCover)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH =
        VH(LayoutInflater.from(parent.context).inflate(ITEM_LAYOUT_RES, parent, false))

    override fun onBindViewHolder(holder: VH, position: Int) {
        val playlist = items[position]
        holder.title.text = playlist.name
        holder.subtitle.visibility = View.VISIBLE
        holder.subtitle.text = "${playlist.itemCount} items"
        ImageResolver.request(holder.cover, playlist.thumbnail, R.drawable.ic_playlist)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { _, focused ->
            holder.cover.isSelected = focused
            if (focused) onFocus(playlist)
        }
        holder.itemView.setOnClickListener { onClick(playlist) }
    }

    override fun getItemCount(): Int = items.size

    fun submit(newItems: List<PlaylistEntity>) {
        items = newItems
        notifyDataSetChanged()
    }
}

class PlaylistMediaAdapter(
    private var items: List<PlaylistMedia>,
    private val onFocus: (PlaylistMedia) -> Unit,
    private val onClick: (PlaylistMedia) -> Unit
) : RecyclerView.Adapter<PlaylistMediaAdapter.VH>() {

    class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.itemTitle)
        val subtitle: TextView = view.findViewById(R.id.itemSubtitle)
        val cover: ImageView = view.findViewById(R.id.itemCover)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH =
        VH(LayoutInflater.from(parent.context).inflate(R.layout.item_content, parent, false))

    override fun onBindViewHolder(holder: VH, position: Int) {
        val media = items[position]
        val title: String
        val subtitle: String
        val image: String?
        val fallback: Int

        when (media) {
            is PlaylistMedia.SongItem -> {
                title = media.value.displayTitle()
                subtitle = media.value.artists.joinToString(" · ") { it.displayTitle() }
                image = media.value.thumb
                fallback = R.drawable.ic_song
            }
            is PlaylistMedia.PodcastItem -> {
                title = media.value.displayTitle()
                subtitle = media.value.artists.joinToString(" · ") { it.displayTitle() }
                image = media.value.thumb
                fallback = R.drawable.ic_song
            }
            is PlaylistMedia.VideoItem -> {
                title = media.value.displayTitle()
                subtitle = media.value.artists.joinToString(" · ") { it.displayTitle() }
                image = media.value.thumb
                fallback = R.drawable.ic_video
            }
            is PlaylistMedia.MusicVideoItem -> {
                title = media.value.displayTitle()
                subtitle = media.value.artists.joinToString(" · ") { it.displayTitle() }
                image = media.value.thumb
                fallback = R.drawable.ic_video
            }
        }

        holder.title.text = title
        holder.subtitle.text = subtitle
        holder.subtitle.visibility = if (subtitle.isBlank()) View.GONE else View.VISIBLE
        ImageResolver.request(holder.cover, image, fallback)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { _, focused ->
            holder.cover.isSelected = focused
            if (focused) onFocus(media)
        }
        holder.itemView.setOnClickListener { onClick(media) }
    }

    override fun getItemCount(): Int = items.size

    fun submit(newItems: List<PlaylistMedia>) {
        items = newItems
        notifyDataSetChanged()
    }
}
