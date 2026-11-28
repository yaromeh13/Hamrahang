package ir.hamrahang.app.ui.player

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.WindowInsetsControllerCompat
import ir.hamrahang.app.databinding.ActivityVideoBinding
import ir.hamrahang.tv.player.PlayerManager

/** Minimal full-screen video surface for the shared player. */
class VideoActivity : AppCompatActivity() {

    private lateinit var binding: ActivityVideoBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityVideoBinding.inflate(layoutInflater)
        setContentView(binding.root)

        WindowCompat.setDecorFitsSystemWindows(window, false)
        WindowInsetsControllerCompat(window, binding.root).apply {
            systemBarsBehavior =
                WindowInsetsControllerCompat.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
            hide(WindowInsetsCompat.Type.systemBars())
        }
    }

    override fun onStart() {
        super.onStart()
        binding.playerView.player = PlayerManager.getPlayer(this)
        binding.playerView.keepScreenOn = true
    }

    override fun onStop() {
        // Rotation recreates the activity; keep playing in that case.
        if (!isChangingConfigurations) PlayerManager.pause()
        binding.playerView.player = null
        super.onStop()
    }
}
