package ir.hamrahang.tv.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.leanback.widget.BaseGridView
import androidx.leanback.widget.HorizontalGridView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.util.ImageResolver

data class LiveOwnerRow(
    val owner: SourceOwner?,
    val items: List<LiveItem>
)

class LiveRowsAdapter(
    private var rows: List<LiveOwnerRow>,
    private val onItemClick: (LiveItem) -> Unit
) : RecyclerView.Adapter<LiveRowsAdapter.RowVH>() {

    inner class RowVH(view: View) : RecyclerView.ViewHolder(view) {
        private val ownerIcon: ImageView = view.findViewById(R.id.liveOwnerIcon)
        private val ownerName: TextView = view.findViewById(R.id.liveOwnerName)
        private val recycler: HorizontalGridView = view.findViewById(R.id.liveCardsRecycler)

        fun bind(row: LiveOwnerRow) {
            ownerName.text = row.owner?.name?.takeIf { it.isNotBlank() } ?: "Unknown Owner"
            ImageResolver.request(
                ownerIcon,
                ImageResolver.owner(row.owner),
                R.drawable.ic_live
            ).centerCrop().into(ownerIcon)

            recycler.setNumRows(1)
            recycler.setHorizontalSpacing(
                (12 * itemView.resources.displayMetrics.density).toInt()
            )
            val edge = itemView.resources.getDimensionPixelSize(R.dimen.content_edge_spacing)
            recycler.setPaddingRelative(edge, 0, edge, 0)
            recycler.setWindowAlignment(BaseGridView.WINDOW_ALIGN_BOTH_EDGE)
            recycler.setWindowAlignmentOffset(0)
            recycler.setWindowAlignmentOffsetPercent(50f)
            recycler.setItemAlignmentOffset(0)
            recycler.setItemAlignmentOffsetPercent(50f)
            recycler.adapter = LiveAdapter(row.items, onItemClick)
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RowVH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_live_row, parent, false)
        return RowVH(view)
    }

    override fun onBindViewHolder(holder: RowVH, position: Int) {
        holder.bind(rows[position])
    }

    override fun getItemCount(): Int = rows.size

    fun submit(newRows: List<LiveOwnerRow>) {
        rows = newRows
        notifyDataSetChanged()
    }
}
