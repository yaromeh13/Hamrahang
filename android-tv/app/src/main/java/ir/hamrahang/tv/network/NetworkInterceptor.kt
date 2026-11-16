package ir.hamrahang.tv.network

import okhttp3.Interceptor
import okhttp3.Response
import java.net.SocketTimeoutException

class NetworkInterceptor : Interceptor {

    override fun intercept(
        chain: Interceptor.Chain
    ): Response {

        return try {

            chain.proceed(chain.request())

        }

        catch (e: SocketTimeoutException) {

            NetworkEvents.reportTimeout()

            throw e

        }

    }

}