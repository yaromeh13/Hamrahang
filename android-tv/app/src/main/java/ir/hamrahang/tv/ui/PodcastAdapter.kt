package ir.hamrahang.tv.ui

import android.util.Log
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

import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.podcast.Podcast
import ir.hamrahang.tv.utils.AppState


class PodcastAdapter(
    private var items: List<Podcast>,
    private val onFocus: (Podcast) -> Unit,
    private val onClick: (Podcast, Int) -> Unit
) : RecyclerView.Adapter<PodcastAdapter.VH>() {
    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_song   // اسم فایل XML واقعی خودت رو بذار
    }
    inner class VH(view: View) : RecyclerView.ViewHolder(view) {

        val title: TextView = view.findViewById(R.id.songTitle)
        val artist: TextView = view.findViewById(R.id.songArtist)
        val cover: ImageView = view.findViewById(R.id.songCover)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_song, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {

        val item = items[position]

        holder.title.text =
            if (AppState.isPersian()) item.titleFa ?: item.title
            else item.title
        holder.artist.text =
            item.artists
                .joinToString(" • ") {

                    if (AppState.isPersian())
                        it.titleFa ?: it.title ?: ""
                    else
                        it.title ?: it.titleFa ?: ""

                }

        Glide.with(holder.itemView)
            .load(item.meta?.tmb)
            .transform(CenterCrop(), RoundedCorners(7))
            .placeholder(android.R.color.darker_gray)
            .error(android.R.color.darker_gray)
            .into(holder.cover)

        holder.itemView.setOnFocusChangeListener { v, hasFocus ->
            holder.cover.isSelected = hasFocus
            if (hasFocus) {
                Log.d("FOCUS_TEST", item.title)
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

    fun submit(newItems: List<Podcast>) {
        items = newItems
        notifyDataSetChanged()
    }
}