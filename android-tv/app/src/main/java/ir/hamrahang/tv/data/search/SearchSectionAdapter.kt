package ir.hamrahang.tv.data.search

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.ui.search.SectionVH

class SearchSectionAdapter(
    private val onItemClick: (Any) -> Unit
) : RecyclerView.Adapter<RecyclerView.ViewHolder>() {

    private val items = mutableListOf<SearchSection>()

    fun submit(list: List<SearchSection>) {
        items.clear()
        items.addAll(list)
        notifyDataSetChanged()
    }

    override fun getItemViewType(position: Int): Int {
        return when (items[position]) {
            is SearchSection.Artists -> 1
            is SearchSection.Songs -> 2
            is SearchSection.Albums -> 3
            is SearchSection.Videos -> 4
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {

        val inflater = LayoutInflater.from(parent.context)

        return when (viewType) {

            1 -> SectionVH(
                inflater.inflate(R.layout.item_section_list, parent, false),
                onItemClick
            )

            2 -> SectionVH(
                inflater.inflate(R.layout.item_section_list, parent, false),
                onItemClick
            )

            3 -> SectionVH(
                inflater.inflate(R.layout.item_section_list, parent, false),
                onItemClick
            )

            else -> SectionVH(
                inflater.inflate(R.layout.item_section_list, parent, false),
                onItemClick
            )
        }
    }

    override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {

        val section = items[position]

        (holder as SectionVH).bind(section)
    }

    override fun getItemCount() = items.size
}