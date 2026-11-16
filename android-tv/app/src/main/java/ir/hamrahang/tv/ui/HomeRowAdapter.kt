package ir.hamrahang.tv.ui

import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.home.HomeRowModel
import ir.hamrahang.tv.domain.home.HomeRowType
import ir.hamrahang.tv.domain.musicvideo.MusicVideo
import ir.hamrahang.tv.domain.hero.HeroContent
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.playback.PlaybackSource


class HomeRowAdapter(
    private var items: List<HomeRowModel>,
    private val onHeroFocus: (HeroContent) -> Unit,
    private val onItemClick: (UiClickEvent) -> Unit
) : RecyclerView.Adapter<HomeRowAdapter.RowVH>() {

    inner class RowVH(view: View) : RecyclerView.ViewHolder(view) {

        private val title: TextView =
            view.findViewById(R.id.rowTitle)

        private val recycler: RecyclerView =
            view.findViewById(R.id.horizontalRecycler)

        private var videoAdapter: MusicVideoAdapter? = null
        private var artistAdapter: ArtistAdapter? = null
        private var songAdapter: SongAdapter? = null
        private var albumAdapter: AlbumAdapter? = null


        fun bind(row: HomeRowModel) {

            // ===== Title =====
            if (row.title == null) {
                title.visibility = View.GONE
            } else {
                title.visibility = View.VISIBLE
                title.setText(row.title)
            }

            // ===== LayoutManager فقط یکبار =====
            if (recycler.layoutManager == null) {
                recycler.layoutManager =
                    LinearLayoutManager(
                        itemView.context,
                        RecyclerView.HORIZONTAL,
                        false
                    )
            }

            // ===== TYPE BASED ADAPTER =====
            when (row.type) {

                HomeRowType.ARTIST -> {
                    if (artistAdapter == null) {

                        artistAdapter = ArtistAdapter(
                            items = row.artists,

                            onFocus = { artist ->
                                onHeroFocus(
                                    HeroContent.ArtistHero(artist)
                                )
                            },

                            onClick = { artist ->
                                onItemClick(
                                    UiClickEvent.ArtistClick(artist)
                                )
                            }
                        )

                        recycler.adapter = artistAdapter
                    } else {
                        artistAdapter?.submit(row.artists)
                    }
                }

                HomeRowType.NEW_SONG -> {

                    if (songAdapter == null) {
                        songAdapter = SongAdapter(
                            items = row.songs,

                            onFocus = { song ->
                                onHeroFocus(
                                    HeroContent.SongHero(song)
                                )
                            },

                            onClick = { song, index ->
                                Log.d(
                                    "HOME",
                                    "row songs=${row.songs.size}"
                                )
                                PlaybackManager.start(

                                    PlaybackSession(

                                        id = row.type.name,

                                        title = row.type.name,

                                        source = PlaybackSource.HOME,

                                        songs = row.songs.toMutableList(),

                                        currentIndex = index
                                    )
                                )

                                onItemClick(
                                    UiClickEvent.SongClick(song)
                                )
                            }
                        )

                        recycler.adapter = songAdapter
                    } else {
                        songAdapter?.submit(row.songs)
                    }
                }

                HomeRowType.NEW_ALBUM -> {

                    if (albumAdapter == null) {
                        albumAdapter = AlbumAdapter(

                            items = row.albums,

                            onFocus = { album ->
                                onHeroFocus(
                                    HeroContent.AlbumHero(album)
                                )
                            },

                            onClick = { album ->
                                onItemClick(
                                    UiClickEvent.AlbumClick(album)
                                )
                            }
                        )
                        recycler.adapter = albumAdapter
                    } else {
                        albumAdapter?.submit(row.albums)
                    }
                }

                else -> {
                    if (videoAdapter == null) {
                        videoAdapter = MusicVideoAdapter(
                            items = row.videos,

                            onFocus = { video ->
                                onHeroFocus(
                                    HeroContent.MusicVideoHero(video)
                                )
                            },

                            onClick = { video ->
                                onItemClick(
                                    UiClickEvent.VideoClick(video)
                                )
                            }
                        )
                        recycler.adapter = videoAdapter
                    } else {
                        videoAdapter?.submit(row.videos)
                    }
                }
            }
        }
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): RowVH {

        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_home_row, parent, false)

        return RowVH(view)
    }

    override fun onBindViewHolder(
        holder: RowVH,
        position: Int
    ) {
        holder.bind(items[position])
    }

    override fun getItemCount() = items.size

    fun submit(newItems: List<HomeRowModel>) {
        items = newItems
        notifyDataSetChanged()
    }
}