package ir.hamrahang.tv.ui.rows

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.leanback.widget.HorizontalGridView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.ui.*
import ir.hamrahang.tv.util.CardHeightCache

class ContentRowsAdapter(
    private var rows: List<ContentRow>,
    private val onHeroFocus: (HeroContent) -> Unit,
    private val onBackgroundChange: (String?) -> Unit,
    private val onItemClick: (UiClickEvent) -> Unit
) : RecyclerView.Adapter<ContentRowsAdapter.RowVH>() {

    inner class RowVH(view: View) : RecyclerView.ViewHolder(view) {
        private val title = view.findViewById<TextView>(R.id.rowTitle)
        private val recycler = view.findViewById<HorizontalGridView>(R.id.horizontalRecycler)

        fun bind(row: ContentRow) {
            if (row.title.isNullOrBlank()) {
                title.visibility = View.GONE
            } else {
                title.visibility = View.VISIBLE
                title.text = row.title
            }

            recycler.setNumRows(1)

            val cardLayoutRes = when (row) {
                is ContentRow.Songs -> {
                    recycler.adapter = SongAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.SongHero(item))
                        },
                        onClick = { item, _ ->
                            onItemClick(UiClickEvent.SongClick(item))
                        }
                    )
                    SongAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Podcasts -> {
                    recycler.adapter = PodcastAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.PodcastHero(item))
                        },
                        onClick = { item, _ ->
                            onItemClick(UiClickEvent.PodcastClick(item))
                        }
                    )
                    PodcastAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Videos -> {
                    recycler.adapter = VideoAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.VideoHero(item))
                        },
                        onClick = { item, _ ->
                            onItemClick(UiClickEvent.VideoClick(item))
                        }
                    )
                    VideoAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.MusicVideos -> {
                    recycler.adapter = VideoAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.MusicVideoHero(item))
                        },
                        onClick = { item, _ ->
                            onItemClick(UiClickEvent.MusicVideoClick(item))
                        }
                    )
                    VideoAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Artists -> {
                    recycler.adapter = ArtistAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.ArtistHero(item))
                        },
                        onClick = { item ->
                            onItemClick(UiClickEvent.ArtistClick(item))
                        }
                    )
                    ArtistAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Albums -> {
                    recycler.adapter = AlbumAdapter(
                        items = row.items,
                        onFocus = { item ->
                            onBackgroundChange(item.heroBackground)
                            onHeroFocus(HeroContent.AlbumHero(item))
                        },
                        onClick = { item ->
                            onItemClick(UiClickEvent.AlbumClick(item))
                        }
                    )
                    AlbumAdapter.ITEM_LAYOUT_RES
                }
            }

            val cardHeight = CardHeightCache.getHeight(
                itemView.context, cardLayoutRes, recycler
            )
            recycler.layoutParams = recycler.layoutParams.apply {
                height = cardHeight
            }
            recycler.requestLayout()
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RowVH {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_row, parent, false)
        return RowVH(view)
    }

    override fun onBindViewHolder(holder: RowVH, position: Int) {
        holder.bind(rows[position])
    }

    override fun getItemCount() = rows.size

    fun submit(newRows: List<ContentRow>) {
        rows = newRows
        notifyDataSetChanged()
    }
}
