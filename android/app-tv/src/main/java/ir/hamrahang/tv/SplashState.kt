package ir.hamrahang.tv

sealed class SplashState {

    object Loading : SplashState()

    object Success : SplashState()

    object Error : SplashState()

    object Maintenance : SplashState()

    object UpdateRequired : SplashState()
}