package ir.hamrahang.app.ui.home

import android.os.Parcelable
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.app.databinding.ItemSectionBinding

class SectionAdapter(
    private val onCardClick: (HomeSection, Int) -> Unit,
    private val onSeeAll: (HomeSection) -> Unit
) : RecyclerView.Adapter<SectionAdapter.Holder>() {

    private var sections: List<HomeSection> = emptyList()
    private val scrollStates = mutableMapOf<String, Parcelable?>()
    private var listWidth = 0

    class Holder(val binding: ItemSectionBinding) : RecyclerView.ViewHolder(binding.root) {
        var sectionId: String? = null
    }

    fun submit(newSections: List<HomeSection>) {
        if (newSections == sections) return
        sections = newSections
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): Holder {
        listWidth = parent.measuredWidth
        val binding =
            ItemSectionBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        binding.cards.layoutManager =
            LinearLayoutManager(parent.context, LinearLayoutManager.HORIZONTAL, false)
        return Holder(binding)
    }

    override fun getItemCount(): Int = sections.size

    override fun onBindViewHolder(holder: Holder, position: Int) {
        val section = sections[position]
        val binding = holder.binding
        holder.sectionId = section.id
        binding.sectionTitle.text = section.title
        binding.seeAll.setOnClickListener { onSeeAll(section) }

        val resources = binding.root.resources
        val screenWidth =
            if (listWidth > 0) listWidth else resources.displayMetrics.widthPixels
        val available = screenWidth - binding.cards.paddingLeft - binding.cards.paddingRight
        val cardWidth =
            (available / CardMetrics.visibleCards(resources.configuration, section.style)).toInt()

        binding.cards.adapter = CardAdapter(section, cardWidth, onCardClick)
        scrollStates[section.id]?.let { binding.cards.layoutManager?.onRestoreInstanceState(it) }
    }

    override fun onViewRecycled(holder: Holder) {
        holder.sectionId?.let {
            scrollStates[it] = holder.binding.cards.layoutManager?.onSaveInstanceState()
        }
    }
}
