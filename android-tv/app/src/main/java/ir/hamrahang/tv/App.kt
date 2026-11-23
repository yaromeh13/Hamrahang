package ir.hamrahang.tv

import android.app.Application
import ir.hamrahang.tv.channel.HomeChannelPublisher
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.playback.PlaybackManager
import ir.hamrahang.tv.util.Ui
import ir.hamrahang.tv.network.NetworkMonitor
import ir.hamrahang.tv.settings.SettingsManager

class App : Application() {

    override fun onCreate() {
        super.onCreate()

        Ui.init(this)
        DatabaseProvider.init(applicationContext)
        NetworkMonitor.init(this)
        SettingsManager.init(this)
        PlaybackManager.init(this)
        HomeChannelPublisher.publish(this)
    }
}