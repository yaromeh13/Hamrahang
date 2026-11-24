package ir.hamrahang.tv.ui

import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.annotation.LayoutRes
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.util.ImageResolver
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.util.displayTitle

class PodcastAdapter(
    private var items: List<Podcast>,
    private val onFocus: (Podcast) -> Unit,
    private val onClick: (Podcast, Int) -> Unit
) : RecyclerView.Adapter<PodcastAdapter.VH>() {
    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_content
    }

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.itemTitle)
        val artist: TextView = view.findViewById(R.id.itemSubtitle)
        val cover: ImageView = view.findViewById(R.id.itemCover)
        val coverFrame: View = view.findViewById(R.id.coverFrame)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(ITEM_LAYOUT_RES, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val item = items[position]
        holder.title.text = item.displayTitle()
        holder.artist.text = item.artists.joinToString(" • ") {
            it.displayTitle()
        }

        ImageResolver.request(holder.cover, item.thumb, R.drawable.ic_song)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { _, hasFocus ->
            holder.coverFrame.isSelected = hasFocus
            if (hasFocus) {
                Log.d("FOCUS_TEST", item.title ?: "")
                onFocus(item)
            }
        }
        holder.itemView.setOnClickListener {
            val index = holder.bindingAdapterPosition
            if (index != RecyclerView.NO_POSITION) onClick(item, index)
        }
    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<Podcast>) {
        items = newItems
        notifyDataSetChanged()
    }
}