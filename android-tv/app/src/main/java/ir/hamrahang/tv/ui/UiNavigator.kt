package ir.hamrahang.tv.ui

import android.util.Log
import androidx.fragment.app.FragmentActivity
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.R
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.data.local.ContentType
import ir.hamrahang.tv.playback.PlaybackFragment
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.player.*
import kotlinx.coroutines.launch

object UiNavigator {

    private var activity: FragmentActivity? = null
    private var pendingDetailSession: PlaybackSession? = null

    fun init(activity: FragmentActivity) {
        this.activity = activity
    }

    /**
     * A destination opened from the fullscreen More panel must be visible.
     * Close the overlay player first, then execute the normal native navigation.
     */
    fun runAfterPlayerDismiss(action: () -> Unit) {
        val act = activity ?: return
        if (act is MainActivity) {
            act.closeFullscreenPlayer()
            act.supportFragmentManager.executePendingTransactions()
        }
        action()
    }

    // ---------------- VIDEO ----------------

    fun openVideo() {

        val act = activity as? MainActivity ?: return
        act.openFullscreenPlayer(PlayerFragment())
    }

    // ---------------- ARTIST ----------------

    fun openArtist(artist: Artist) {

        val act = activity ?: return

        act.supportFragmentManager.beginTransaction()
            .replace(
                R.id.contentContainer,
                ArtistFragment.newInstance(artist.id)
                //ArtistFragment.newInstance(artist.id)
            )
            .addToBackStack("artist")
            .commit()
    }

    // ---------------- Generic media details ----------------

    fun openMediaDetail(
        type: ContentType,
        id: String,
        session: PlaybackSession? = null
    ) {
        val act = activity ?: return
        pendingDetailSession = session
        act.supportFragmentManager.beginTransaction()
            .replace(
                R.id.contentContainer,
                MediaDetailFragment.newInstance(type, id)
            )
            .addToBackStack("media_detail")
            .commit()
    }

    fun consumePendingDetailSession(): PlaybackSession? {
        val session = pendingDetailSession
        pendingDetailSession = null
        return session
    }

    // ---------------- Playback ----------------
    fun openPlayback(){
        val act = activity ?: return

        act.supportFragmentManager.beginTransaction()
            .replace(
                R.id.contentContainer,
                PlaybackFragment()
            )
            .addToBackStack("playback")
            .commit()
    }
    fun openPlayback(album: Album) {

        val act = activity ?: return

        act.supportFragmentManager.beginTransaction()
            .replace(
                R.id.contentContainer,
                PlaybackFragment()
            )
            .addToBackStack("playback")
            .commit()

        act.lifecycleScope.launch {
            PlaybackManager.startAlbum(album)
        }
    }

    fun onBack() {
        val act = activity ?: return
        val fm = act.supportFragmentManager

        if (fm.backStackEntryCount > 0) {
            fm.popBackStack()
        }
    }

    fun openFullscreenPlayer() {
        val act = activity as? MainActivity ?: return
        act.openFullscreenPlayer(PlayerFragment())
    }

}

