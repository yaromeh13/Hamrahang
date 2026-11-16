package ir.hamrahang.tv.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.annotation.LayoutRes
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.R
import ir.hamrahang.tv.util.displayTitle



class ArtistAdapter(
    private var items: List<Artist>,
    private val onFocus: (Artist) -> Unit,
    private val onClick: (Artist) -> Unit
) : RecyclerView.Adapter<ArtistAdapter.VH>() {
    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_artist
    }
    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val image: ImageView = view.findViewById(R.id.artistImage)
        val name: TextView = view.findViewById(R.id.artistTitle)
        val subname: TextView = view.findViewById(R.id.artistSubTitle)
        val container: View = view.findViewById(R.id.artistContainer)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(ITEM_LAYOUT_RES, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {

        val item = items[position]

        holder.name.text = item.displayTitle()
        //holder.subname.text = item.title

        Glide.with(holder.itemView)
            .load(item.thumb)
            .placeholder(android.R.color.darker_gray)
            .error(android.R.color.darker_gray)
            .circleCrop()
            .into(holder.image)

        holder.itemView.setOnFocusChangeListener { v, hasFocus ->
            holder.container.isSelected = hasFocus
            if (hasFocus) {
                onFocus(item)
            }

        }
        holder.itemView.setOnClickListener {
            onClick(item)
        }

    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<Artist>) {
        items = newItems
        notifyDataSetChanged()
    }
}