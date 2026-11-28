package ir.hamrahang.tv.ui.settings

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.DecelerateInterpolator
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R

data class SettingCategory(

    val title: String,

    val icon: Int

)

class SettingsCategoryAdapter(

    private val items: List<SettingCategory>,

    private val onSelected: (Int) -> Unit

) : RecyclerView.Adapter<SettingsCategoryAdapter.Holder>() {

    private var selected = 0

    inner class Holder(v: View) : RecyclerView.ViewHolder(v) {

        val root = v.findViewById<View>(R.id.root)

        val icon = v.findViewById<ImageView>(R.id.icon)

        val title = v.findViewById<TextView>(R.id.title)

        init {

            root.setOnFocusChangeListener { _, hasFocus ->

                animateFocus(icon, hasFocus)

                if (hasFocus) {

                    val old = selected

                    selected = bindingAdapterPosition

                    notifyItemChanged(old)
                    notifyItemChanged(selected)

                    onSelected(selected)

                }
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
                    R.layout.item_setting_category,
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

        holder.icon.setImageResource(item.icon)

        holder.root.isSelected = position == selected
    }

    private fun animateFocus(
        view: View,
        focused: Boolean
    ) {

        val scale =
            if (focused) 1.35f
            else 1f

        view.animate()

            .scaleX(scale)

            .scaleY(scale)

            .setInterpolator(
                DecelerateInterpolator()
            )

            .setDuration(170)

            .start()
    }
}
