package ir.hamrahang.app

import android.app.Application
import android.widget.Toast
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.network.NetworkMonitor
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerNotifier
import ir.hamrahang.tv.settings.SettingsManager

class App : Application() {

    override fun onCreate() {
        super.onCreate()

        DatabaseProvider.init(applicationContext)
        NetworkMonitor.init(this)
        SettingsManager.init(this)

        PlayerManager.notifier = object : PlayerNotifier {
            override fun info(text: String) = toast(text)
            override fun warning(text: String) = toast(text)
            override fun error(text: String) = toast(text)
        }
        PlaybackManager.init(this)
    }

    private fun toast(text: String) {
        Toast.makeText(this, text, Toast.LENGTH_SHORT).show()
    }
}
