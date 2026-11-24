package ir.hamrahang.tv

import android.animation.Animator
import android.content.Intent
import android.os.Bundle
import android.view.View
import com.google.android.material.button.MaterialButton
import android.widget.TextView
import androidx.lifecycle.lifecycleScope
import com.airbnb.lottie.LottieAnimationView
import ir.hamrahang.tv.network.ApiClient
import ir.hamrahang.tv.utils.AppState
import kotlinx.coroutines.launch

class SplashActivity : BaseActivity() {

    private val viewModel by lazy {
        SplashViewModel(AppRepository(ApiClient.pingApi))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)

        val lottie = findViewById<LottieAnimationView>(R.id.lottieView)

        lottie.setAnimation("splash.json")
        lottie.playAnimation()

        lottie.addAnimatorListener(object : Animator.AnimatorListener {

            override fun onAnimationEnd(animation: Animator) {
                viewModel.checkServer()
            }

            override fun onAnimationStart(animation: Animator) {}
            override fun onAnimationCancel(animation: Animator) {}
            override fun onAnimationRepeat(animation: Animator) {}
        })

        observeState()
    }

    private fun observeState() {
        lifecycleScope.launch {
            viewModel.state.collect { state ->
                when (state) {
                    is SplashState.Loading -> Unit

                    is SplashState.Success -> {
                        AppState.offlineMode = false
                        goToMain()
                    }

                    is SplashState.Error -> showRetry()
                    is SplashState.Maintenance -> showMaintenance()
                    is SplashState.UpdateRequired -> showUpdate()
                }
            }
        }
    }

    private fun goToMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }

    private fun showRetry() {
        val container = findViewById<View>(R.id.retryContainer)
        val btn = findViewById<MaterialButton>(R.id.retryBtn)
        val offline = findViewById<MaterialButton>(R.id.offlineBtn)
        val text = findViewById<TextView>(R.id.errorText)

        container.visibility = View.VISIBLE
        text.text = getString(R.string.splash_error_connection)

        btn.requestFocus()

        btn.setOnClickListener {
            container.visibility = View.GONE
            viewModel.retry()
        }

        // Retry is the initial D-pad focus; Offline remains the second choice.
        offline.setOnClickListener {
            AppState.offlineMode = true
            goToMain()
        }
    }

    private fun showMaintenance() {
        val container = findViewById<View>(R.id.retryContainer)
        val text = findViewById<TextView>(R.id.errorText)

        container.visibility = View.VISIBLE
        text.text = getString(R.string.splash_maintenance)
    }

    private fun showUpdate() {
        val container = findViewById<View>(R.id.retryContainer)
        val btn = findViewById<MaterialButton>(R.id.retryBtn)
        val text = findViewById<TextView>(R.id.errorText)

        container.visibility = View.VISIBLE
        text.text = getString(R.string.splash_update_available)
        btn.setText(R.string.splash_update)

        btn.requestFocus()

        btn.setOnClickListener {
            viewModel.retry()
        }
    }
}
