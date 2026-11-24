package ir.hamrahang.tv.network

import okhttp3.Interceptor
import okhttp3.Response
import java.io.IOException

class NetworkInterceptor : Interceptor {

    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        val isHamrahangApi = request.url.host.equals("hamrahang.ir", ignoreCase = true)

        return try {
            val response = chain.proceed(request)
            if (isHamrahangApi && response.code < 500) {
                // A non-server-error response proves the API endpoint is responding.
                NetworkEvents.reportApiAvailable()
            } else if (isHamrahangApi) {
                NetworkEvents.reportApiUnavailable()
            }
            response
        } catch (error: IOException) {
            if (isHamrahangApi) {
                NetworkEvents.reportApiUnavailable()
            }
            if (error is java.net.SocketTimeoutException) {
                NetworkEvents.reportTimeout()
            }
            throw error
        }
    }
}
