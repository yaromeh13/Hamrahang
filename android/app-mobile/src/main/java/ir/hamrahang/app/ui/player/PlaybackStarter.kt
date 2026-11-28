package ir.hamrahang.app.ui.player

import android.content.Context
import android.content.Intent
import android.widget.Toast
import ir.hamrahang.app.R
import ir.hamrahang.app.ui.home.CardStyle
import ir.hamrahang.app.ui.home.HomeSection
import ir.hamrahang.tv.playback.PlaybackItem
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.playback.PlaybackSession
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerManager

object PlaybackStarter {

    /** Plays the clicked card with the rest of its section as the queue. */
    fun open(context: Context, section: HomeSection, index: Int) {
        if (section.cards.getOrNull(index)?.contentType == null) {
            Toast.makeText(context, R.string.coming_soon, Toast.LENGTH_SHORT).show()
            return
        }
        val video = section.style == CardStyle.WIDE
        val items = section.cards.mapNotNull { card ->
            val type = card.contentType ?: return@mapNotNull null
            PlaybackItem(
                id = card.id,
                title = card.title,
                artwork = card.image,
                artists = card.artists,
                contentType = type,
                playCount = card.playCount
            )
        }
        // The manager does not restart the item that is already loaded; resume it instead.
        val alreadyLoaded = PlayerManager.getCurrentMedia()?.id == items[index].id
        PlaybackManager.start(
            PlaybackSession(
                id = section.id,
                title = section.title,
                source = section.source,
                items = items,
                currentIndex = index,
                mediaType = if (video) MediaType.VIDEO else MediaType.AUDIO
            )
        )
        if (alreadyLoaded) PlayerManager.resume()
        if (video) context.startActivity(Intent(context, VideoActivity::class.java))
    }
}
