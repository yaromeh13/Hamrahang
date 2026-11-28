package ir.hamrahang.tv.util

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ir.hamrahang.tv.network.NetworkEvents
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.launch
import java.net.SocketTimeoutException

fun ViewModel.safeLaunch(
    block: suspend () -> Unit
) {

    viewModelScope.launch {

        try {

            block()

        }

        catch (e: SocketTimeoutException) {

            NetworkEvents.reportTimeout()

        }

        catch (e: CancellationException) {

            throw e

        }

        catch (e: Exception) {

            e.printStackTrace()

        }

    }

}