package ir.hamrahang.tv.ui.rows

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.hero.HeroContent
import android.view.KeyEvent
import androidx.leanback.widget.HorizontalGridView
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.ui.*
import ir.hamrahang.tv.util.CardHeightCache
import ir.hamrahang.tv.util.displayTitle

class ContentRowsAdapter(

    private var rows: List<ContentRow>,

    private val onHeroFocus: (HeroContent) -> Unit,

    private val onBackgroundChange: (String?) -> Unit,

    private val onItemClick: (UiClickEvent) -> Unit,


) : RecyclerView.Adapter<ContentRowsAdapter.RowVH>() {
    inner class RowVH(view: View) : RecyclerView.ViewHolder(view) {

        private val title = view.findViewById<TextView>(R.id.rowTitle)
        private val recycler = view.findViewById<HorizontalGridView>(R.id.horizontalRecycler)

        fun bind(row: ContentRow) {

            // ===== Title =====
            if (row.title.isNullOrBlank()) {
                title.visibility = View.GONE
            } else {
                title.visibility = View.VISIBLE
                title.text = row.title
            }

            // ===== LayoutManager =====
            recycler.setNumRows(1)

            // ===== TYPE HANDLING (when به‌عنوان expression) =====
            val cardLayoutRes: Int = when (row) {

                is ContentRow.Songs -> {
                    recycler.adapter = SongAdapter(
                        items = row.items,
                        onFocus = { song ->
                            onBackgroundChange(song.thumb)
                            onHeroFocus(HeroContent.SongHero(song))
                        },
                        onClick = { song, index ->

                            PlaybackManager.start(
                                PlaybackSession(
                                    id = row.sessionId,
                                    title = row.sessionTitle,
                                    source = row.sessionSource,
                                    items = row.items.map { item ->
                                        PlaybackItem(
                                            id = item.id,
                                            title = item.displayTitle(),
                                            artwork = item.thumb,
                                            artists = item.artists
                                        )
                                    },
                                    currentIndex = index,
                                    mediaType = MediaType.AUDIO
                                )
                            )
                            onItemClick(
                                UiClickEvent.SongClick(song)
                            )
                        }
                    )

                    SongAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Podcasts -> {
                    recycler.adapter = PodcastAdapter(
                        items = row.items,
                        onFocus = { podcast ->
                            onBackgroundChange(podcast.meta?.tmb)
                            onHeroFocus(HeroContent.PodcastHero(podcast))
                        },
                        onClick = { podcast, index ->
                            PlaybackManager.start(
                                PlaybackSession(
                                    id = row.sessionId,
                                    title = row.sessionTitle,
                                    source = row.sessionSource,
                                    items = row.items.map { item ->
                                        PlaybackItem(
                                            id = item.id,
                                            title = item.titleFa ?: item.title,
                                            artwork = item.thumb,
                                            artists = item.artists,
                                            contentType = ContentType.PODCAST
                                        )
                                    },
                                    currentIndex = index,
                                    mediaType = MediaType.AUDIO
                                )
                            )
                            onItemClick(UiClickEvent.PodcastClick(podcast))
                        }
                    )
                    PodcastAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Videos -> {
                    recycler.adapter = VideoAdapter(
                        items = row.items,
                        onFocus = { video ->
                            onBackgroundChange(video.thumb)
                            onHeroFocus(HeroContent.VideoHero(video))
                        },
                        onClick = { video, index ->
                            PlaybackManager.start(
                                PlaybackSession(
                                    id = row.sessionId,
                                    title = row.sessionTitle,
                                    source = row.sessionSource,
                                    items = row.items.map { item ->
                                        PlaybackItem(
                                            id = item.id,
                                            title = item.displayTitle(),
                                            artwork = item.thumb,
                                            artists = item.artists,
                                            contentType = item.contentType
                                        )
                                    },
                                    currentIndex = index,
                                    mediaType = MediaType.VIDEO
                                )
                            )
                            onItemClick(UiClickEvent.VideoClick(video))
                        }
                    )
                    VideoAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.MusicVideos -> {
                    recycler.adapter = VideoAdapter(
                        items = row.items,
                        onFocus = { video ->
                            onBackgroundChange(video.thumb)
                            onHeroFocus(HeroContent.MusicVideoHero(video))
                        },
                        onClick = { video, index ->
                            PlaybackManager.start(
                                PlaybackSession(
                                    id = row.sessionId,
                                    title = row.sessionTitle,
                                    source = row.sessionSource,
                                    items = row.items.map { item ->
                                        PlaybackItem(
                                            id = item.id,
                                            title = item.displayTitle(),
                                            artwork = item.thumb,
                                            artists = item.artists,
                                            contentType = item.contentType
                                        )
                                    },
                                    currentIndex = index,
                                    mediaType = MediaType.VIDEO
                                )
                            )
                            onItemClick(UiClickEvent.MusicVideoClick(video))
                        }
                    )
                    VideoAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Artists -> {
                    recycler.adapter = ArtistAdapter(
                        items = row.items,
                        onFocus = { artist ->
                            onBackgroundChange(artist.thumb)
                            onHeroFocus(HeroContent.ArtistHero(artist))
                        },
                        onClick = { artist ->
                            onItemClick(UiClickEvent.ArtistClick(artist))
                        }
                    )
                    ArtistAdapter.ITEM_LAYOUT_RES
                }

                is ContentRow.Albums -> {
                    recycler.adapter = AlbumAdapter(
                        items = row.items,
                        onFocus = { album ->
                            onBackgroundChange(album.thumb)
                            onHeroFocus(HeroContent.AlbumHero(album))
                        },
                        onClick = { album ->
                            onItemClick(UiClickEvent.AlbumClick(album))
                        }
                    )
                    AlbumAdapter.ITEM_LAYOUT_RES
                }
            }

            // ===== Dynamic row height based on actual card content =====
            val cardHeight = CardHeightCache.getHeight(
                itemView.context, cardLayoutRes, recycler
            )
            recycler.layoutParams = recycler.layoutParams.apply {
                height = cardHeight
            }
            recycler.requestLayout()
        }
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): RowVH {

        val view = LayoutInflater
            .from(parent.context)
            .inflate(R.layout.item_row, parent, false)

        return RowVH(view)
    }

    override fun onBindViewHolder(
        holder: RowVH,
        position: Int
    ) {
        holder.bind(rows[position])
    }

    override fun getItemCount() = rows.size

    fun submit(newRows: List<ContentRow>) {
        rows = newRows
        notifyDataSetChanged()
    }
}