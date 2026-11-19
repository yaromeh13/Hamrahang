package ir.hamrahang.tv.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.annotation.LayoutRes
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.util.ImageResolver
import ir.hamrahang.tv.domain.album.Album
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.util.displayTitle

class AlbumAdapter(
    private var items: List<Album>,
    private val onFocus: (Album) -> Unit,
    private val onClick: (Album) -> Unit
) : RecyclerView.Adapter<AlbumAdapter.VH>() {
    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_content
    }

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.itemTitle)
        val cover: ImageView = view.findViewById(R.id.itemCover)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_content, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val item = items[position]
        holder.title.text = item.displayTitle()

        ImageResolver.request(holder.cover, item.thumb, R.drawable.ic_album_disk)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { _, hasFocus ->
            holder.cover.isSelected = hasFocus
            if (hasFocus) onFocus(item)
        }

        holder.itemView.setOnClickListener {
            onClick(item)
        }
    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<Album>) {
        items = newItems
        notifyDataSetChanged()
    }
}