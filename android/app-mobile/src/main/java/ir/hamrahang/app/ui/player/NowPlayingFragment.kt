package ir.hamrahang.app.ui.player

import android.os.Bundle
import android.view.View
import android.widget.ImageButton
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.bumptech.glide.Glide
import com.google.android.material.imageview.ShapeableImageView
import ir.hamrahang.app.R
import ir.hamrahang.tv.player.PlaybackState
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import ir.hamrahang.tv.util.displayTitle
import kotlinx.coroutines.launch

/** Simple full-screen now-playing view opened from the landscape disc. */
class NowPlayingFragment : Fragment(R.layout.fragment_now_playing) {

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        val art = view.findViewById<ShapeableImageView>(R.id.nowPlayingArt)
        val title = view.findViewById<TextView>(R.id.nowPlayingTitle)
        val subtitle = view.findViewById<TextView>(R.id.nowPlayingSubtitle)
        val playPause = view.findViewById<ImageButton>(R.id.nowPlayingPlayPause)

        view.findViewById<ImageButton>(R.id.nowPlayingBack).setOnClickListener {
            parentFragmentManager.popBackStack()
        }
        playPause.setOnClickListener { PlayerManager.toggle() }

        viewLifecycleOwner.lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    PlayerState.playbackInfo.collect { info ->
                        title.text = info.title
                        subtitle.text = info.artists.joinToString(" · ") { it.displayTitle() }
                            .ifBlank { info.mediaId.orEmpty() }
                        Glide.with(art)
                            .load(info.artwork)
                            .placeholder(R.mipmap.ic_launcher)
                            .error(R.mipmap.ic_launcher)
                            .circleCrop()
                            .into(art)
                    }
                }
                launch {
                    PlayerState.uiState.collect { state ->
                        playPause.setImageResource(
                            if (state.playbackState == PlaybackState.PLAYING) R.drawable.ic_pause
                            else R.drawable.ic_play
                        )
                    }
                }
            }
        }
    }
}
