package ir.hamrahang.app.ui.player

import android.content.ComponentName
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.WindowCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.WindowInsetsControllerCompat
import androidx.media3.session.MediaController
import androidx.media3.session.SessionToken
import com.google.common.util.concurrent.ListenableFuture
import ir.hamrahang.app.databinding.ActivityVideoBinding
import ir.hamrahang.app.playback.MobilePlaybackService
import java.util.concurrent.Executor

/** Full-screen video surface controlled through the mobile MediaSession. */
class VideoActivity : AppCompatActivity() {

    private lateinit var binding: ActivityVideoBinding
    private var controllerFuture: ListenableFuture<MediaController>? = null
    private var mediaController: MediaController? = null
    private val directExecutor = Executor { command -> command.run() }

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
        val token = SessionToken(this, ComponentName(this, MobilePlaybackService::class.java))
        val future = MediaController.Builder(this, token).buildAsync()
        controllerFuture = future
        future.addListener({
            if (controllerFuture === future) {
                runCatching { future.get() }.onSuccess { controller ->
                    mediaController = controller
                    binding.playerView.player = controller
                }
            }
        }, directExecutor)
        binding.playerView.keepScreenOn = true
    }

    override fun onStop() {
        if (!isChangingConfigurations) mediaController?.pause()
        binding.playerView.player = null
        controllerFuture?.let { MediaController.releaseFuture(it) }
        controllerFuture = null
        mediaController = null
        super.onStop()
    }
}
