package ir.hamrahang.tv.network

import android.content.Context
import android.net.*
import android.os.Build
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

object NetworkMonitor {

    private val _isConnected =
        MutableStateFlow(true)

    val isConnected: StateFlow<Boolean>
        get() = _isConnected

    fun init(
        context: Context
    ) {

        val manager =
            context.getSystemService(
                Context.CONNECTIVITY_SERVICE
            ) as ConnectivityManager

        //--------------------------------------------------
        // Initial State
        //--------------------------------------------------

        _isConnected.value =
            isConnected(manager)

        //--------------------------------------------------
        // Listen Network Changes
        //--------------------------------------------------

        val request =
            NetworkRequest.Builder()
                .addCapability(
                    NetworkCapabilities.NET_CAPABILITY_INTERNET
                )
                .build()

        manager.registerNetworkCallback(

            request,

            object : ConnectivityManager.NetworkCallback() {

                override fun onAvailable(
                    network: Network
                ) {

                    _isConnected.value = true

                    NetworkEvents.reportInternetAvailable()

                }

                override fun onLost(
                    network: Network
                ) {

                    val connected =
                        isConnected(manager)

                    _isConnected.value =
                        connected

                    if (connected) {

                        NetworkEvents.reportInternetAvailable()

                    } else {

                        NetworkEvents.reportInternetLost()

                    }

                }

            }

        )

    }

    //--------------------------------------------------

    private fun isConnected(
        manager: ConnectivityManager
    ): Boolean {

        return if (
            Build.VERSION.SDK_INT >= Build.VERSION_CODES.M
        ) {

            val network =
                manager.activeNetwork ?: return false

            val caps =
                manager.getNetworkCapabilities(network)

            caps?.hasCapability(
                NetworkCapabilities.NET_CAPABILITY_INTERNET
            ) == true

        } else {

            @Suppress("DEPRECATION")
            val info =
                manager.activeNetworkInfo

            @Suppress("DEPRECATION")
            info != null && info.isConnected

        }

    }

}

/*

package ir.hamrahang.tv.network

import android.content.Context
import android.net.*
import android.os.Build
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

object NetworkMonitor {

    private val _isConnected = MutableStateFlow(true)

    val isConnected: StateFlow<Boolean>
        get() = _isConnected

    fun init(context: Context) {

        val manager =
            context.getSystemService(Context.CONNECTIVITY_SERVICE)
                    as ConnectivityManager

        //--------------------------------------------------
        // Initial State
        //--------------------------------------------------

        _isConnected.value = isConnected(manager)

        //--------------------------------------------------
        // Listen Network Changes
        //--------------------------------------------------

        val request =
            NetworkRequest.Builder()
                .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                .build()

        manager.registerNetworkCallback(
            request,
            object : ConnectivityManager.NetworkCallback() {

                override fun onAvailable(network: Network) {

                    _isConnected.value = true

                }

                override fun onLost(network: Network) {

                    _isConnected.value = isConnected(manager)

                }

            }
        )

    }

    //--------------------------------------------------

    private fun isConnected(
        manager: ConnectivityManager
    ): Boolean {

        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {

            val network = manager.activeNetwork ?: return false

            val caps =
                manager.getNetworkCapabilities(network)

            caps?.hasCapability(
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

/*
package ir.hamrahang.tv.network

import android.content.Context
import android.net.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

object NetworkMonitor {

    private val _isConnected = MutableStateFlow(true)

    val isConnected: StateFlow<Boolean>
        get() = _isConnected

    fun init(context: Context) {

        val manager =
            context.getSystemService(Context.CONNECTIVITY_SERVICE)
                    as ConnectivityManager

        val request =
            NetworkRequest.Builder()
                .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                .build()

        manager.registerNetworkCallback(
            request,
            object : ConnectivityManager.NetworkCallback() {

                override fun onAvailable(network: Network) {

                    _isConnected.value = true

                }

                override fun onLost(network: Network) {

                    _isConnected.value = false

                }

            }
        )

        val active = manager.activeNetwork

        val caps = manager.getNetworkCapabilities(active)

        _isConnected.value =
            caps?.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                ?: false
    }
}*/