package ir.hamrahang.tv

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class SplashViewModel(
    private val repo: AppRepository
) : ViewModel() {

    private val _state = MutableStateFlow<SplashState>(SplashState.Loading)
    val state: StateFlow<SplashState> = _state

    fun checkServer() {
        viewModelScope.launch {
            try {
                android.util.Log.d("PING_TEST", "request start")

                val response = repo.ping()

                android.util.Log.d(
                    "PING_TEST",
                    "code = ${response.code()}"
                )
                android.util.Log.d(
                    "PING_TEST",
                    "body = ${response.body()}"
                )
                if (response.isSuccessful) {

                    val body = response.body()

                    _state.value = when {

                        body?.maintenance == true -> SplashState.Maintenance

                        body?.forceUpdate == true -> SplashState.UpdateRequired

                        body?.status == "ok" -> SplashState.Success

                        else -> SplashState.Error
                    }

                } else {
                    _state.value = SplashState.Error
                }

            } catch (e: Exception) {
                android.util.Log.e(
                    "PING_TEST",
                    e.javaClass.simpleName + " : " + e.message,
                    e
                )
                _state.value = SplashState.Error
            }
        }
    }

    fun retry() {
        _state.value = SplashState.Loading
        checkServer()
    }
}