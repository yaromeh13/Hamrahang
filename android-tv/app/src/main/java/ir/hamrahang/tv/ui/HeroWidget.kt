package ir.hamrahang.tv.ui.hero

import android.util.Log
import android.view.KeyEvent
import android.view.LayoutInflater
import android.view.View
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.constraintlayout.widget.ConstraintLayout

import androidx.lifecycle.LifecycleOwner
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.util.ImageResolver
import com.bumptech.glide.load.resource.bitmap.CenterCrop
import com.bumptech.glide.load.resource.bitmap.RoundedCorners

import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.local.DatabaseUpdater
import ir.hamrahang.tv.data.album.AlbumRepository
import ir.hamrahang.tv.data.artist.ArtistRepository
import ir.hamrahang.tv.hero.HeroManager
import ir.hamrahang.tv.hero.HeroStyle
import ir.hamrahang.tv.ui.widget.FadeImageView

import ir.hamrahang.tv.util.displayTitle
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.launch

class HeroWidget(

    root: View,
    owner: LifecycleOwner

) {

    private val videoHero: View = root.findViewById(R.id.musicVideoHero)
    private val songHero: View = root.findViewById(R.id.songHero)
    private val artistHero: View = root.findViewById(R.id.artistHero)
    private val albumHero: View = root.findViewById(R.id.albumHero)

    // VIDEO
    private val VideoTitle: TextView = root.findViewById(R.id.VideoTitle)
    private val VideoSubTitle: TextView = root.findViewById(R.id.VideoSubTitle)
    private val VideoArt: FadeImageView = root.findViewById(R.id.VideoArt)
//    private val VideoArt: ImageView =        root.findViewById(R.id.VideoArt)
    private val layoutVideoArtists: LinearLayout = root.findViewById(R.id.layoutVideoArtists)
    private val videoMetaBinder = HeroMetaBinder(root.findViewById(R.id.videoMetaContainer))

    // SONG
    private val songTitle: TextView = root.findViewById(R.id.songTitle)
    private val layoutSongArtists: LinearLayout = root.findViewById(R.id.layoutSongArtists)
    private val songCover: ImageView = root.findViewById(R.id.songCover)
    private val metaBinder = HeroMetaBinder(root.findViewById(R.id.metaContainer))

    // ALBUM
    private val albumTitle: TextView = root.findViewById(R.id.albumTitle)

    private val albumArtists: LinearLayout = root.findViewById(R.id.albumArtist)
    private val albumMetaBinder = HeroMetaBinder(root.findViewById(R.id.albumMetaContainer))

    private val albumCover: ImageView = root.findViewById(R.id.albumCover)

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
                        VideoTitle.text = musicVideo.displayTitle()
                        VideoSubTitle.text = musicVideo.title ?: ""

                        ImageResolver.request(VideoArt, musicVideo.thumb, R.drawable.ic_video)
                            .into(VideoArt)

                        VideoArt.fadeStart = 0.4f
                        videoMetaBinder.bind(musicVideo.meta)

                        HeroArtistsBinder.bind(
                            layoutVideoArtists,
                            musicVideo.artists

                        )
                    }

                    HeroStyle.SONG -> {

                        showSong()
                        val song = state.song ?: return@collect
                        songTitle.text = song.displayTitle()
                        ImageResolver.request(songCover, song.thumb, R.drawable.ic_song)
                            .transform(
                                CenterCrop(),
                                RoundedCorners(24)
                            )
                            .into(songCover)

                        HeroArtistsBinder.bind(
                            layoutSongArtists,
                            song.artists

                        )
                        metaBinder.bind(song.meta)

                    }

                    HeroStyle.ARTIST -> {

                        showArtist()
                        val artist = state.artist ?: return@collect
                        artistName.text = artist.displayTitle()
                        val globalName = artist.name?.trim().orEmpty()
                        val localizedTitle = artist.displayTitle().trim()
                        if (globalName.isNotEmpty() && !globalName.equals(localizedTitle, ignoreCase = true)) {
                            artistSubName.text = globalName
                            artistSubName.visibility = View.VISIBLE
                        } else {
                            artistSubName.text = ""
                            artistSubName.visibility = View.GONE
                        }

                        artistBio.text = artist.meta?.des.orEmpty()

                        ImageResolver.request(artistCover, artist.thumb, R.drawable.ic_profile)
                            .circleCrop()
                            .into(artistCover)

                        artistDetailsBinder.bind(artist)
                    }

                    HeroStyle.ALBUM -> {

                        showAlbum()
                        val album = state.album ?: return@collect

                        albumTitle.text = album.displayTitle()
                        albumMetaBinder.bind(album.meta)

                        ImageResolver.request(albumCover, album.thumb, R.drawable.ic_album_disk)
                            .transform(
                                CenterCrop(),
                                RoundedCorners(24)
                            )
                            .into(albumCover)

                        owner.lifecycleScope.launch {
                            var artists = ArtistRepository().getByIds(album.artists)

                            // Load album details if one or more related artists are missing locally.
                            if (artists.size < album.artists.size && album.artists.isNotEmpty()) {
                                runCatching {
                                    AlbumRepository().getAlbum(album.id)
                                }
                                artists = ArtistRepository().getByIds(album.artists)
                            }

                            HeroArtistsBinder.bind(albumArtists, artists)
                        }
                    }
                }
            }
        }
    }

    private fun hideAll() {

        videoHero.visibility = View.GONE
        songHero.visibility = View.GONE
        artistHero.visibility = View.GONE
        albumHero.visibility = View.GONE
    }

    private fun showVideo() {
        updateVideoLayout()
        hideAll()
        videoHero.visibility = View.VISIBLE
        //updateVideoLayout()
    }

    private fun showSong() {

        hideAll()
        songHero.visibility = View.VISIBLE
    }
    private fun showAlbum() {

        hideAll()
        albumHero.visibility = View.VISIBLE
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
                keyCode == KeyEvent.KEYCODE_DPAD_DOWN) {

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

        val lp = VideoArt.layoutParams as ConstraintLayout.LayoutParams

        lp.startToStart = ConstraintLayout.LayoutParams.UNSET
        lp.endToEnd = ConstraintLayout.LayoutParams.PARENT_ID

        VideoArt.layoutParams = lp
    }


}