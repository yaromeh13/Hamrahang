package ir.hamrahang.tv.network

import android.content.Context
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import android.os.Build
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

object NetworkMonitor {

    private val _isConnected = MutableStateFlow(false)
    val isConnected: StateFlow<Boolean>
        get() = _isConnected

    @Volatile
    private var initialized = false

    fun init(context: Context) {
        if (initialized) return
        synchronized(this) {
            if (initialized) return
            initialized = true
        }

        val manager = context.applicationContext.getSystemService(
            Context.CONNECTIVITY_SERVICE
        ) as ConnectivityManager

        updateConnectionState(manager)

        val request = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .build()

        manager.registerNetworkCallback(
            request,
            object : ConnectivityManager.NetworkCallback() {
                override fun onAvailable(network: Network) {
                    updateConnectionState(manager)
                }

                override fun onLost(network: Network) {
                    updateConnectionState(manager)
                }

                override fun onCapabilitiesChanged(
                    network: Network,
                    networkCapabilities: NetworkCapabilities
                ) {
                    updateConnectionState(manager)
                }
            }
        )
    }

    private fun updateConnectionState(manager: ConnectivityManager) {
        val connected = hasNetworkConnection(manager)
        val previous = _isConnected.value
        if (previous == connected) return

        _isConnected.value = connected
        if (connected) {
            NetworkEvents.reportInternetAvailable()
        } else {
            NetworkEvents.reportInternetLost()
        }
    }

    /**
     * This is deliberately only a device-network check. It does not validate
     * Internet access or the availability of https://hamrahang.ir.
     */
    private fun hasNetworkConnection(manager: ConnectivityManager): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val network = manager.activeNetwork ?: return false
            val capabilities = manager.getNetworkCapabilities(network)
            capabilities?.hasCapability(
                NetworkCapabilities.NET_CAPABILITY_INTERNET
            ) == true
        } else {
            @Suppress("DEPRECATION")
            val info = manager.activeNetworkInfo
            @Suppress("DEPRECATION")
            info != null && info.isConnected
        }
    }
}
