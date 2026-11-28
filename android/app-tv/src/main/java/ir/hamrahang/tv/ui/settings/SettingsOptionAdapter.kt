package ir.hamrahang.tv.ui.settings

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.DecelerateInterpolator
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R

data class SettingOption(
    val id: String,
    val title: String,
    var selected: Boolean = false
)

class SettingsOptionAdapter(

    private var items: MutableList<SettingOption>,

    private val onOptionSelected: (SettingOption) -> Unit

) : RecyclerView.Adapter<SettingsOptionAdapter.Holder>() {

    inner class Holder(v: View) : RecyclerView.ViewHolder(v) {

        val root = v.findViewById<View>(R.id.root)

        val title = v.findViewById<TextView>(R.id.title)

        val check = v.findViewById<ImageView>(R.id.check)

        init {

            root.setOnFocusChangeListener { _, hasFocus ->

                animateFocus(root, hasFocus)

            }

            root.setOnClickListener {

                select(bindingAdapterPosition)

            }
        }
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): Holder {

        return Holder(

            LayoutInflater.from(parent.context)

                .inflate(
                    R.layout.item_setting_option,
                    parent,
                    false
                )
        )
    }

    override fun getItemCount() = items.size

    override fun onBindViewHolder(
        holder: Holder,
        position: Int
    ) {

        val item = items[position]

        holder.title.text = item.title

        holder.root.isSelected = item.selected

        holder.check.visibility =
            if (item.selected)
                View.VISIBLE
            else
                View.GONE
    }

    private fun select(position: Int) {

        items.forEachIndexed { index, option ->

            option.selected = index == position

        }

        notifyDataSetChanged()

        onOptionSelected(items[position])

    }

    fun submit(newItems: List<SettingOption>) {

        items = newItems.toMutableList()

        notifyDataSetChanged()

    }

    private fun animateFocus(
        view: View,
        focused: Boolean
    ) {

        val scale =
            if (focused) 1.03f
            else 1f

        view.animate()

            .scaleX(scale)

            .scaleY(scale)

            .setDuration(140)

            .setInterpolator(
                DecelerateInterpolator()
            )

            .start()
    }
}