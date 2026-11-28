package ir.hamrahang.app.ui.home

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.app.R
import ir.hamrahang.app.databinding.ItemCardBinding

class CardAdapter(
    private val section: HomeSection,
    private val cardWidth: Int,
    private val onClick: (HomeSection, Int) -> Unit
) : RecyclerView.Adapter<CardAdapter.Holder>() {

    class Holder(val binding: ItemCardBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): Holder {
        val binding = ItemCardBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        binding.root.layoutParams = binding.root.layoutParams.apply { width = cardWidth }
        val imageWidth = cardWidth - binding.root.paddingLeft - binding.root.paddingRight
        binding.cardImage.layoutParams = binding.cardImage.layoutParams.apply {
            height = (imageWidth * section.style.heightRatio).toInt()
        }
        return Holder(binding)
    }

    override fun getItemCount(): Int = section.cards.size

    override fun onBindViewHolder(holder: Holder, position: Int) {
        val card = section.cards[position]
        val binding = holder.binding
        binding.cardTitle.text = card.title
        binding.cardSubtitle.text = card.subtitle
        binding.cardSubtitle.isVisible = section.style != CardStyle.CIRCLE

        val request = Glide.with(binding.cardImage)
            .load(card.image?.takeIf { it.isNotBlank() })
            .placeholder(R.drawable.bg_artwork_placeholder)
            .error(R.drawable.bg_artwork_placeholder)
            .fallback(R.drawable.bg_artwork_placeholder)
        if (section.style == CardStyle.CIRCLE) {
            request.circleCrop().into(binding.cardImage)
        } else {
            val radius = (8 * binding.root.resources.displayMetrics.density).toInt()
            request.transform(CenterCrop(), RoundedCorners(radius)).into(binding.cardImage)
        }

        holder.itemView.setOnClickListener {
            val index = holder.bindingAdapterPosition
            if (index != RecyclerView.NO_POSITION) onClick(section, index)
        }
    }
}
