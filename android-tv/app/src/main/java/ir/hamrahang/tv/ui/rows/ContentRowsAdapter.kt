package ir.hamrahang.tv.ui.rows

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.leanback.widget.HorizontalGridView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.ui.*
import ir.hamrahang.tv.util.CardHeightCache
import ir.hamrahang.tv.util.displayTitle

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
                val compactPadding = 0
                itemView.setPadding(
                    itemView.paddingLeft,
                    compactPadding,
                    itemView.paddingRight,
                    compactPadding
                )
            } else {
                title.visibility = View.VISIBLE
                title.text = row.title
                val rowPadding = itemView.resources.getDimensionPixelSize(R.dimen._18dp)
                itemView.setPadding(
                    itemView.paddingLeft,
                    rowPadding,
                    itemView.paddingRight,
                    rowPadding
                )
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
                        onClick = { item, index ->
                            val session = PlaybackSession(
                                id = row.sessionId,
                                title = row.sessionTitle,
                                source = row.sessionSource,
                                items = row.items.map { song ->
                                    PlaybackItem(
                                        id = song.id,
                                        title = song.displayTitle(),
                                        artwork = song.thumb,
                                        artists = song.artists,
                                        contentType = ContentType.SONG
                                    )
                                },
                                currentIndex = index,
                                mediaType = MediaType.AUDIO
                            )
                            onItemClick(UiClickEvent.SongClick(item, session))
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
                        onClick = { item, index ->
                            val session = PlaybackSession(
                                id = row.sessionId,
                                title = row.sessionTitle,
                                source = row.sessionSource,
                                items = row.items.map { podcast ->
                                    PlaybackItem(
                                        id = podcast.id,
                                        title = podcast.displayTitle(),
                                        artwork = podcast.thumb,
                                        artists = podcast.artists,
                                        contentType = ContentType.PODCAST
                                    )
                                },
                                currentIndex = index,
                                mediaType = MediaType.AUDIO
                            )
                            onItemClick(UiClickEvent.PodcastClick(item, session))
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
                        onClick = { item, index ->
                            val session = PlaybackSession(
                                id = row.sessionId,
                                title = row.sessionTitle,
                                source = row.sessionSource,
                                items = row.items.map { video ->
                                    PlaybackItem(
                                        id = video.id,
                                        title = video.displayTitle(),
                                        artwork = video.thumb,
                                        artists = video.artists,
                                        contentType = ContentType.VIDEO
                                    )
                                },
                                currentIndex = index,
                                mediaType = MediaType.VIDEO
                            )
                            onItemClick(UiClickEvent.VideoClick(item, session))
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
                        onClick = { item, index ->
                            val session = PlaybackSession(
                                id = row.sessionId,
                                title = row.sessionTitle,
                                source = row.sessionSource,
                                items = row.items.map { video ->
                                    PlaybackItem(
                                        id = video.id,
                                        title = video.displayTitle(),
                                        artwork = video.thumb,
                                        artists = video.artists,
                                        contentType = ContentType.MUSICVIDEO
                                    )
                                },
                                currentIndex = index,
                                mediaType = MediaType.VIDEO
                            )
                            onItemClick(UiClickEvent.MusicVideoClick(item, session))
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
