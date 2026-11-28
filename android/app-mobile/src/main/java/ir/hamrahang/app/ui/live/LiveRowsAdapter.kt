package ir.hamrahang.app.ui.live

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.app.R
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.player.MediaType

class LiveRowsAdapter(
    private val onClick: (LiveItem, List<LiveItem>) -> Unit
) : RecyclerView.Adapter<LiveRowsAdapter.RowHolder>() {

    private var rows: List<LiveOwnerRow> = emptyList()

    class RowHolder(view: View) : RecyclerView.ViewHolder(view) {
        val ownerArtwork: ImageView = view.findViewById(R.id.liveOwnerArtwork)
        val ownerName: TextView = view.findViewById(R.id.liveOwnerName)
        val mediaLabel: TextView = view.findViewById(R.id.liveMediaLabel)
        val cards: RecyclerView = view.findViewById(R.id.liveCards)
    }

    fun submit(items: List<LiveOwnerRow>) {
        rows = items
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RowHolder =
        RowHolder(LayoutInflater.from(parent.context).inflate(R.layout.item_live_owner_mobile, parent, false))

    override fun getItemCount(): Int = rows.size

    override fun onBindViewHolder(holder: RowHolder, position: Int) {
        val row = rows[position]
        holder.ownerName.text = row.ownerName
        holder.mediaLabel.setText(
            if (row.mediaType == MediaType.VIDEO) R.string.live_video else R.string.live_audio
        )
        Glide.with(holder.ownerArtwork)
            .load(row.ownerArtwork?.takeIf { it.isNotBlank() })
            .placeholder(R.drawable.bg_artwork_placeholder)
            .error(R.drawable.bg_artwork_placeholder)
            .circleCrop()
            .into(holder.ownerArtwork)

        val cardWidth = if (row.mediaType == MediaType.VIDEO) {
            (196 * holder.itemView.resources.displayMetrics.density).toInt()
        } else {
            (148 * holder.itemView.resources.displayMetrics.density).toInt()
        }
        holder.cards.layoutManager = LinearLayoutManager(
            holder.itemView.context, RecyclerView.HORIZONTAL, false
        )
        holder.cards.adapter = LiveCardAdapter(row, cardWidth) { item ->
            onClick(item, row.items)
        }
    }

    private class LiveCardAdapter(
        private val row: LiveOwnerRow,
        private val cardWidth: Int,
        private val onClick: (LiveItem) -> Unit
    ) : RecyclerView.Adapter<LiveCardAdapter.CardHolder>() {

        class CardHolder(view: View) : RecyclerView.ViewHolder(view) {
            val artwork: ImageView = view.findViewById(R.id.liveCardArtwork)
            val title: TextView = view.findViewById(R.id.liveCardTitle)
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CardHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_live_station_mobile, parent, false)
            view.layoutParams = RecyclerView.LayoutParams(
                cardWidth,
                ViewGroup.LayoutParams.WRAP_CONTENT
            ).apply {
                marginEnd = (12 * parent.resources.displayMetrics.density).toInt()
            }
            return CardHolder(view)
        }

        override fun getItemCount(): Int = row.items.size

        override fun onBindViewHolder(holder: CardHolder, position: Int) {
            val item = row.items[position]
            holder.title.text = item.title
            val imageHeight = if (row.mediaType == MediaType.VIDEO) {
                (cardWidth * 9f / 16f).toInt()
            } else {
                cardWidth
            }
            holder.artwork.layoutParams = holder.artwork.layoutParams.apply {
                height = imageHeight
            }
            val radius = (10 * holder.itemView.resources.displayMetrics.density).toInt()
            Glide.with(holder.artwork)
                .load(item.thumb.takeIf { it.isNotBlank() })
                .placeholder(R.drawable.bg_artwork_placeholder)
                .error(R.drawable.bg_artwork_placeholder)
                .transform(CenterCrop(), RoundedCorners(radius))
                .into(holder.artwork)
            holder.itemView.setOnClickListener {
                val index = holder.bindingAdapterPosition
                if (index != RecyclerView.NO_POSITION) onClick(item)
            }
        }
    }
}
