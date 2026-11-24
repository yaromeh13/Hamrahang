package ir.hamrahang.tv.ui

import android.util.Log
import android.view.KeyEvent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import ir.hamrahang.tv.util.ImageResolver
import android.widget.TextView
import androidx.annotation.LayoutRes
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.video.Video
import ir.hamrahang.tv.util.displayTitle

class VideoAdapter(
    private var items: List<Video>,
    private val onFocus: (Video) -> Unit,
    private val onClick: (Video, Int) -> Unit
) : RecyclerView.Adapter<VideoAdapter.VH>() {
    companion object {
        @LayoutRes
        val ITEM_LAYOUT_RES = R.layout.item_musicvideo
    }
    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.songTitle)
        val cover: ImageView = view.findViewById(R.id.cover)
        val coverFrame: View = view.findViewById(R.id.coverFrame)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_musicvideo, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {

        val item = items[position]

        holder.title.text = item.displayTitle()

        ImageResolver.request(holder.cover, item.thumb, R.drawable.ic_video)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        holder.cover.clipToOutline = true
        holder.itemView.setOnFocusChangeListener { _, hasFocus ->

            holder.coverFrame.isSelected = hasFocus

            if (hasFocus) {
                Log.d("FOCUS_TEST", item.displayTitle())
                onFocus(item)
            }
        }

        holder.itemView.setOnClickListener {
            val index = holder.bindingAdapterPosition
            if (index != RecyclerView.NO_POSITION) onClick(item, index)
        }


    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<Video>) {
        items = newItems
        notifyDataSetChanged()
    }
}
