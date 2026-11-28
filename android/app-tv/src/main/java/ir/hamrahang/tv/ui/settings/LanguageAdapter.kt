package ir.hamrahang.tv.ui.settings

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.ui.settings.LanguageManager
import ir.hamrahang.tv.R

class LanguageAdapter(

    private val items: List<LanguageItem>,

    private val onClick: (LanguageItem) -> Unit

) : RecyclerView.Adapter<LanguageAdapter.VH>() {

    inner class VH(view: View) : RecyclerView.ViewHolder(view) {

        val root: View = view.findViewById(R.id.root)

        val flag: ImageView = view.findViewById(R.id.imgFlag)

        val title: TextView = view.findViewById(R.id.txtName)

    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): VH {

        val view =
            LayoutInflater.from(parent.context)
                .inflate(
                    R.layout.item_language,
                    parent,
                    false
                )

        return VH(view)

    }

    override fun onBindViewHolder(
        holder: VH,
        position: Int
    ) {

        val item = items[position]

        holder.flag.setImageResource(item.flag)

        holder.title.text = item.title
        val current = LanguageManager.getLanguage()
        /*val current =
            LanguageManager.getLanguage(
                holder.itemView.context
            )*/

        holder.itemView.isSelected =
            current == item.code

        holder.itemView.setOnClickListener {

            onClick(item)

        }

        holder.itemView.setOnFocusChangeListener { _, hasFocus ->

            holder.itemView.animate()
                .scaleX(if (hasFocus) 1.10f else 1f)
                .scaleY(if (hasFocus) 1.10f else 1f)
                .translationY(if (hasFocus) -6f else 0f)
                .setDuration(140)
                .start()

        }

    }

    fun indexOf(code: String): Int {

        return items.indexOfFirst {

            it.code == code

        }

    }

    override fun getItemCount() =
        items.size

}