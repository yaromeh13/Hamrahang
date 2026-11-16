package ir.hamrahang.tv.ui

import android.view.KeyEvent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.annotation.LayoutRes
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.R
import ir.hamrahang.tv.util.displayTitle



class SongAdapter(
    private var items: List<Song>,
    private val onFocus: (Song) -> Unit,
    private val onClick: (Song, Int) -> Unit
) : RecyclerView.Adapter<SongAdapter.VH>() {

    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_song  //  منبع حقیقت برای لایوت
    }

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.songCover)
    }
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(ITEM_LAYOUT_RES, parent, false)   // به جای item_song، از ثابت بالا استفاده شد
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {

        val item = items[position]

        holder.title.text = item.displayTitle()
        //holder.artist.text = item.artists.joinToString(" - ") { it.displayTitle() }

        Glide.with(holder.itemView)
            .load(item.thumb)
            .transform(CenterCrop(), RoundedCorners(7))
            .placeholder(android.R.color.darker_gray)
            .error(android.R.color.darker_gray)
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { v, hasFocus ->
            holder.cover.isSelected = hasFocus
            if (hasFocus) {
                android.util.Log.d("FOCUS_TEST", item.displayTitle())
                onFocus(item)
            }
        }
        holder.itemView.setOnClickListener {
            val index = holder.bindingAdapterPosition
            if (index != RecyclerView.NO_POSITION) {
                onClick(item, index)
            }
        }
    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<Song>) {
        items = newItems
        notifyDataSetChanged()
    }


}