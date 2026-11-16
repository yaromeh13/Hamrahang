package ir.hamrahang.tv.ui.search

import android.view.View
import android.widget.TextView
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.search.SearchSection
import ir.hamrahang.tv.ui.ArtistAdapter
import ir.hamrahang.tv.ui.SongAdapter
import ir.hamrahang.tv.ui.AlbumAdapter
import ir.hamrahang.tv.ui.MusicVideoAdapter

class SectionVH(
    itemView: View,
    private val onItemClick: (Any) -> Unit
) : RecyclerView.ViewHolder(itemView) {

    private val title: TextView = itemView.findViewById(R.id.sectionTitle)
    private val recycler: RecyclerView = itemView.findViewById(R.id.sectionRecycler)

    fun bind(section: SearchSection) {

        recycler.layoutManager =
            LinearLayoutManager(itemView.context, LinearLayoutManager.HORIZONTAL, false)

        when (section) {

            is SearchSection.Artists -> {
                title.text = "Artists"

                val adapter = ArtistAdapter(
                    items = section.items,
                    onFocus = onItemClick,
                    onClick = TODO()
                )

                recycler.adapter = adapter
            }

            is SearchSection.Songs -> {
                title.text = "Songs"

                val adapter = SongAdapter(
                    items = section.items,
                    onFocus = onItemClick,
                    onClick = TODO()
                )

                recycler.adapter = adapter
            }

            is SearchSection.Albums -> {
                title.text = "Albums"

                val adapter = AlbumAdapter(
                    items = section.items,
                    onFocus = onItemClick,
                    onClick = TODO()
                )

                recycler.adapter = adapter
            }

            is SearchSection.Videos -> {
                title.text = "Videos"

                val adapter = MusicVideoAdapter(
                    items = section.items,
                    onFocus = onItemClick,
                    onClick = TODO()
                )

                recycler.adapter = adapter
            }
        }
    }
}