package ir.hamrahang.tv.ui

import android.graphics.Color
import android.graphics.Typeface
import android.graphics.drawable.GradientDrawable
import android.os.Bundle
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.FrameLayout
import android.widget.LinearLayout
import android.widget.TextView
import androidx.fragment.app.Fragment
import ir.hamrahang.tv.MainActivity
import ir.hamrahang.tv.R
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.player.MediaType
import ir.hamrahang.tv.player.PlayerFragment
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerMedia
import ir.hamrahang.tv.data.local.ContentType

class LiveFragment : Fragment(R.layout.fragment_live) {

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val context = requireContext()
        val root = view as? FrameLayout ?: return

        val card = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.CENTER
            setPadding(dp(28), dp(22), dp(28), dp(22))
            isFocusable = true
            isFocusableInTouchMode = true
            isClickable = true
            background = cardBackground(false)
        }

        val title = TextView(context).apply {
            text = "Hamrahang Live — Test"
            setTextColor(Color.WHITE)
            textSize = 22f
            typeface = Typeface.DEFAULT_BOLD
            gravity = Gravity.CENTER
        }

        val subtitle = TextView(context).apply {
            text = "Tap the card or PLAY to start the live stream"
            setTextColor(Color.LTGRAY)
            textSize = 14f
            gravity = Gravity.CENTER
            setPadding(0, dp(8), 0, dp(18))
        }

        val playButton = TextView(context).apply {
            text = "▶  PLAY"
            setTextColor(Color.WHITE)
            textSize = 17f
            typeface = Typeface.DEFAULT_BOLD
            gravity = Gravity.CENTER
            setPadding(dp(24), dp(12), dp(24), dp(12))
            background = GradientDrawable().apply {
                setColor(Color.rgb(125, 25, 35))
                cornerRadius = dp(24).toFloat()
            }
            isFocusable = true
            isClickable = true
        }

        val startPlayback = View.OnClickListener {
            playLiveTest()
        }
        card.setOnClickListener(startPlayback)
        playButton.setOnClickListener(startPlayback)

        card.setOnFocusChangeListener { focusedView, hasFocus ->
            focusedView.background = cardBackground(hasFocus)
        }
        card.setOnKeyListener { _, keyCode, event ->
            if (event.action == android.view.KeyEvent.ACTION_DOWN &&
                (keyCode == android.view.KeyEvent.KEYCODE_DPAD_CENTER ||
                 keyCode == android.view.KeyEvent.KEYCODE_ENTER)
            ) {
                playLiveTest()
                true
            } else {
                false
            }
        }

        card.addView(title)
        card.addView(subtitle)
        card.addView(playButton)

        root.addView(
            card,
            FrameLayout.LayoutParams(
                dp(430),
                ViewGroup.LayoutParams.WRAP_CONTENT,
                Gravity.CENTER
            )
        )

        card.post { card.requestFocus() }
    }

    private fun playLiveTest() {
        val streamUrl =
            "https://api.radiomusic.app/v3/Mix/radio_live.m3u8?dt=${System.currentTimeMillis()}"

        val media = PlayerMedia(
            id = "hamrahang_live_test",
            title = "Hamrahang Live",
            artist = "Live Radio",
            artwork = null,
            sources = listOf(MediaSource(url = streamUrl)),
            type = MediaType.AUDIO,
            contentType = ContentType.SONG
        )

        PlayerManager.play(requireContext(), media)
        (activity as? MainActivity)?.openFullscreenPlayer(PlayerFragment())
    }

    private fun cardBackground(focused: Boolean) = GradientDrawable().apply {
        setColor(Color.rgb(34, 34, 40))
        cornerRadius = dp(18).toFloat()
        setStroke(
            dp(if (focused) 3 else 1),
            if (focused) Color.WHITE else Color.rgb(85, 85, 95)
        )
    }

    private fun dp(value: Int): Int =
        (value * resources.displayMetrics.density).toInt()
}
