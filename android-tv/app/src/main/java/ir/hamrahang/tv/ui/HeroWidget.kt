package ir.hamrahang.tv.ui.hero

import android.view.KeyEvent
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.constraintlayout.widget.ConstraintLayout
import androidx.lifecycle.LifecycleOwner
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.hero.HeroStyle
import ir.hamrahang.tv.ui.widget.FadeImageView
import ir.hamrahang.tv.util.ImageResolver
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.launch

class HeroWidget(
    root: View,
    owner: LifecycleOwner
) {

    private val videoHero: View = root.findViewById(R.id.musicVideoHero)
    private val itemHero: View = root.findViewById(R.id.itemHero)
    private val artistHero: View = root.findViewById(R.id.artistHero)

    // VIDEO
    private val videoTitle: TextView = root.findViewById(R.id.VideoTitle)
    private val videoSubTitle: TextView = root.findViewById(R.id.VideoSubTitle)
    private val videoArt: FadeImageView = root.findViewById(R.id.VideoArt)
    private val layoutVideoArtists: LinearLayout =
        root.findViewById(R.id.layoutVideoArtists)
    private val videoMetaContainer: LinearLayout =
        videoHero.findViewById(R.id.itemMetaContainer)

    // SHARED ITEM: SONG / PODCAST / ALBUM / PLAYLIST
    private val itemTitle: TextView = root.findViewById(R.id.itemTitle)
    private val itemSubTitle: TextView = root.findViewById(R.id.itemSubTitle)
    private val layoutItemArtists: LinearLayout =
        root.findViewById(R.id.layoutItemArtists)
    private val itemCover: ImageView = root.findViewById(R.id.itemCover)
    private val itemMetaContainer: LinearLayout =
        itemHero.findViewById(R.id.itemMetaContainer)

    private val heroMetaBinder = HeroMetaBinder()

    // ARTIST
    private val artistName: TextView = root.findViewById(R.id.artistName)
    private val artistSubName: TextView = root.findViewById(R.id.artistSubName)
    private val artistBio: TextView = root.findViewById(R.id.artistBio)
    private val artistCover: ImageView = root.findViewById(R.id.artistCover)

    private lateinit var artistDetailsBinder: HeroArtistDetailsBinder
    private val btnPlayArtist: ImageView = root.findViewById(R.id.btnPlayArtist)

    init {
        artistDetailsBinder = HeroArtistDetailsBinder(
            root.findViewById(R.id.layoutArtistCounts),
            root.findViewById(R.id.layoutArtistInfo),
            root.findViewById(R.id.layoutArtistSocial)
        )

        owner.lifecycleScope.launch {
            HeroManager.engine.state.collect { state ->
                if (state == null) return@collect

                when (state.style) {
                    HeroStyle.VIDEO -> {
                        showVideo()

                        val musicVideo = state.musicVideo ?: return@collect
                        videoTitle.text = musicVideo.displayTitle()
                        videoSubTitle.text = musicVideo.title.orEmpty()

                        ImageResolver.request(
                            videoArt,
                            musicVideo.thumb,
                            R.drawable.ic_video
                        ).into(videoArt)

                        videoArt.fadeStart = 0.4f
                        heroMetaBinder.bind(videoMetaContainer, musicVideo.meta)
                        HeroArtistsBinder.bind(
                            layoutVideoArtists,
                            musicVideo.artists
                        )
                    }

                    HeroStyle.SONG -> {
                        val song = state.song ?: return@collect
                        showItem()

                        bindItemTitle(song.displayTitle(), song.name)
                        ImageResolver.request(
                            itemCover,
                            song.thumb,
                            R.drawable.ic_song
                        ).transform(
                            CenterCrop(),
                            RoundedCorners(24)
                        ).into(itemCover)

                        heroMetaBinder.bind(itemMetaContainer, song.meta)
                        HeroArtistsBinder.bind(
                            layoutItemArtists,
                            song.artists
                        )
                    }

                    HeroStyle.PODCAST -> {
                        val podcast = state.podcast ?: return@collect
                        showItem()

                        bindItemTitle(podcast.displayTitle(), podcast.name)
                        ImageResolver.request(
                            itemCover,
                            podcast.thumb,
                            R.drawable.ic_song
                        ).transform(
                            CenterCrop(),
                            RoundedCorners(24)
                        ).into(itemCover)

                        heroMetaBinder.bind(itemMetaContainer, podcast.meta)
                        HeroArtistsBinder.bind(
                            layoutItemArtists,
                            podcast.artists
                        )
                    }

                    HeroStyle.ALBUM -> {
                        val album = state.album ?: return@collect
                        showItem()

                        bindItemTitle(album.displayTitle(), album.name)
                        ImageResolver.request(
                            itemCover,
                            album.thumb,
                            R.drawable.ic_album_disk
                        ).transform(
                            CenterCrop(),
                            RoundedCorners(24)
                        ).into(itemCover)

                        heroMetaBinder.bind(itemMetaContainer, album.meta)

                        owner.lifecycleScope.launch {
                            var artists = ArtistRepository().getByIds(album.artists)

                            // Fill missing local artist data when album details are available.
                            if (artists.size < album.artists.size && album.artists.isNotEmpty()) {
                                runCatching {
                                    AlbumRepository().getAlbum(album.id)
                                }
                                artists = ArtistRepository().getByIds(album.artists)
                            }

                            // A slower album lookup must not overwrite artists for a newer focus.
                            val currentState = HeroManager.engine.state.value
                            if (currentState?.style == HeroStyle.ALBUM &&
                                currentState.album?.id == album.id
                            ) {
                                HeroArtistsBinder.bind(layoutItemArtists, artists)
                            }
                        }
                    }

                    HeroStyle.ARTIST -> {
                        showArtist()

                        val artist = state.artist ?: return@collect
                        artistName.text = artist.displayTitle()

                        val globalName = artist.name?.trim().orEmpty()
                        val localizedTitle = artist.displayTitle().trim()
                        if (globalName.isNotEmpty() &&
                            !globalName.equals(localizedTitle, ignoreCase = true)
                        ) {
                            artistSubName.text = globalName
                            artistSubName.visibility = View.VISIBLE
                        } else {
                            artistSubName.text = ""
                            artistSubName.visibility = View.GONE
                        }

                        artistBio.text = artist.meta?.des.orEmpty()

                        ImageResolver.request(
                            artistCover,
                            artist.thumb,
                            R.drawable.ic_artist
                        ).circleCrop().into(artistCover)

                        artistDetailsBinder.bind(artist)
                    }
                }
            }
        }
    }


    private fun bindItemTitle(localizedTitle: String, globalName: String?) {
        itemTitle.text = localizedTitle

        val global = globalName?.trim().orEmpty()
        val localized = localizedTitle.trim()

        if (global.isNotEmpty() &&
            !global.equals(localized, ignoreCase = true)
        ) {
            itemSubTitle.text = global
            itemSubTitle.visibility = View.VISIBLE
        } else {
            itemSubTitle.text = ""
            itemSubTitle.visibility = View.GONE
        }
    }

    private fun hideAll() {
        videoHero.visibility = View.GONE
        itemHero.visibility = View.GONE
        artistHero.visibility = View.GONE
    }

    private fun showVideo() {
        updateVideoLayout()
        hideAll()
        videoHero.visibility = View.VISIBLE
    }

    private fun showItem() {
        hideAll()
        itemHero.visibility = View.VISIBLE
    }

    private fun showArtist() {
        hideAll()
        artistHero.visibility = View.VISIBLE
    }

    fun showPlayButton() {
        btnPlayArtist.visibility = View.VISIBLE
    }

    fun hidePlayButton() {
        btnPlayArtist.visibility = View.GONE
    }

    fun setPlayClick(action: () -> Unit) {
        btnPlayArtist.setOnClickListener {
            action()
        }
    }

    fun requestPlayButtonFocus() {
        btnPlayArtist.post {
            btnPlayArtist.requestFocus()
        }
    }

    fun setPlayButtonKeyListener(rows: RecyclerView) {
        btnPlayArtist.setOnKeyListener { _, keyCode, event ->
            if (event.action == KeyEvent.ACTION_DOWN &&
                keyCode == KeyEvent.KEYCODE_DPAD_DOWN
            ) {
                rows.findViewHolderForAdapterPosition(0)
                    ?.itemView
                    ?.requestFocus()
                true
            } else {
                false
            }
        }
    }

    private fun updateVideoLayout() {
        val lp = videoArt.layoutParams as ConstraintLayout.LayoutParams
        lp.startToStart = ConstraintLayout.LayoutParams.UNSET
        lp.endToEnd = ConstraintLayout.LayoutParams.PARENT_ID
        videoArt.layoutParams = lp
    }
}
