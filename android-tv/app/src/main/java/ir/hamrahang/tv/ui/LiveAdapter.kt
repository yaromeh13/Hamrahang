package ir.hamrahang.tv.ui

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.KeyEvent
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.live.LiveItem
import ir.hamrahang.tv.util.ImageResolver

class LiveAdapter(
    private var items: List<LiveItem>,
    private val onClick: (LiveItem) -> Unit,
    private val onFocus: (LiveItem) -> Unit,
    private val isFirstRow: Boolean = false
) : RecyclerView.Adapter<LiveAdapter.VH>() {

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {
        val title: TextView = view.findViewById(R.id.itemTitle)
        val subtitle: TextView = view.findViewById(R.id.itemSubtitle)
        val cover: ImageView = view.findViewById(R.id.itemCover)
        val coverFrame: View = view.findViewById(R.id.coverFrame)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_content, parent, false)
        return VH(view)
    }

    override fun onBindViewHolder(holder: VH, position: Int) {
        val item = items[position]
        holder.title.text = item.title
        holder.subtitle.text = ""

        ImageResolver.request(holder.cover, item.thumb, R.drawable.ic_live)
            .transform(CenterCrop(), RoundedCorners(7))
            .into(holder.cover)

        // Handle navigation on the focused card itself. Leanback's nested
        // HorizontalGridView can consume DPAD before nextFocusUpId is followed.
        holder.itemView.setOnKeyListener { card, keyCode, event ->
            if (event.action != KeyEvent.ACTION_DOWN) {
                return@setOnKeyListener false
            }

            val position = holder.bindingAdapterPosition
            if (position == RecyclerView.NO_POSITION) {
                return@setOnKeyListener false
            }

            when (keyCode) {
                KeyEvent.KEYCODE_DPAD_UP -> {
                    if (isFirstRow) {
                        card.rootView.findViewById<View>(R.id.liveSearchSubmit)
                            ?.requestFocus()
                        true
                    } else {
                        false
                    }
                }

                KeyEvent.KEYCODE_DPAD_LEFT -> {
                    val rtl = card.layoutDirection == View.LAYOUT_DIRECTION_RTL
                    val menuEdgePosition = if (rtl) itemCount - 1 else 0
                    if (position == menuEdgePosition) {
                        (card.context as? android.app.Activity)
                            ?.let { it as? MainActivity }
                            ?.onReturnToMenu()
                        true
                    } else {
                        false
                    }
                }

                else -> false
            }
        }

        holder.itemView.setOnFocusChangeListener { _, hasFocus ->
            holder.coverFrame.isSelected = hasFocus
            if (hasFocus) {
                onFocus(item)
            }
        }
        holder.itemView.setOnClickListener { onClick(item) }
    }

    override fun getItemCount(): Int = items.size

    fun submit(newItems: List<LiveItem>) {
        items = newItems
        notifyDataSetChanged()
    }
}
