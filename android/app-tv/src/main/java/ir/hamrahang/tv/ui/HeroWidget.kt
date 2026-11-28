package ir.hamrahang.tv.ui.hero

import android.view.KeyEvent
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import com.google.android.material.button.MaterialButton
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
    private val heroArt: FadeImageView = root.findViewById(R.id.heroArt)
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
    private val btnFollowArtist: MaterialButton = root.findViewById(R.id.btnFollowArtist)

    init {
        btnFollowArtist.visibility = View.GONE
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

                        bindHeroArt(
                            ImageResolver.hero(musicVideo.meta) ?: musicVideo.thumb,
                            R.drawable.ic_video
                        )
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
                        bindHeroArt(ImageResolver.hero(song.meta), R.drawable.ic_song)
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

                    HeroStyle.PLAYLIST -> {
                        val playlist = state.playlist ?: return@collect
                        showItem()
                        bindItemTitle(playlist.name, "${playlist.itemCount} items")
                        ImageResolver.request(
                            itemCover,
                            playlist.thumbnail,
                            R.drawable.ic_playlist
                        ).transform(
                            CenterCrop(),
                            RoundedCorners(24)
                        ).into(itemCover)
                        itemMetaContainer.visibility = View.GONE
                        layoutItemArtists.visibility = View.GONE
                    }

                    HeroStyle.PODCAST -> {
                        val podcast = state.podcast ?: return@collect
                        showItem()

                        bindItemTitle(podcast.displayTitle(), podcast.name)
                        bindHeroArt(ImageResolver.hero(podcast.meta), R.drawable.ic_song)
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
                        bindHeroArt(ImageResolver.hero(album.meta), R.drawable.ic_album_disk)
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

                        bindHeroArt(ImageResolver.hero(artist.meta), R.drawable.ic_artist)

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

    private fun bindHeroArt(url: String?, fallbackDrawable: Int) {
        if (url.isNullOrBlank()) {
            heroArt.visibility = View.GONE
            heroArt.setImageDrawable(null)
            return
        }

        heroArt.visibility = View.VISIBLE
        heroArt.fadeStart = 0.4f
        ImageResolver.request(heroArt, url, fallbackDrawable).into(heroArt)
    }

    private fun hideAll() {
        videoHero.visibility = View.GONE
        itemHero.visibility = View.GONE
        artistHero.visibility = View.GONE
    }

    private fun showVideo() {
        hideAll()
        heroArt.visibility = View.GONE
        videoHero.visibility = View.VISIBLE
    }

    private fun showItem() {
        hideAll()
        heroArt.visibility = View.GONE
        itemMetaContainer.visibility = View.VISIBLE
        layoutItemArtists.visibility = View.VISIBLE
        itemHero.visibility = View.VISIBLE
    }

    private fun showArtist() {
        hideAll()
        heroArt.visibility = View.GONE
        artistHero.visibility = View.VISIBLE
    }

    fun showPlayButton() {
        btnPlayArtist.visibility = View.VISIBLE
    }

    fun hidePlayButton() {
        btnPlayArtist.visibility = View.GONE
    }

    fun showFollowButton() {
        btnFollowArtist.visibility = View.VISIBLE
    }

    fun hideFollowButton() {
        btnFollowArtist.visibility = View.GONE
    }

    fun setFollowClick(action: () -> Unit) {
        btnFollowArtist.setOnClickListener { action() }
    }

    fun setArtistFollowed(followed: Boolean) {
        btnFollowArtist.isSelected = followed
        btnFollowArtist.setText(if (followed) R.string.following else R.string.follow)
    }

    fun setFollowButtonKeyListener(rows: RecyclerView) {
        btnFollowArtist.setOnKeyListener { _, keyCode, event ->
            if (event.action == KeyEvent.ACTION_DOWN &&
                keyCode == KeyEvent.KEYCODE_DPAD_DOWN
            ) {
                rows.findViewHolderForAdapterPosition(0)?.itemView?.requestFocus()
                true
            } else {
                false
            }
        }
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

}
