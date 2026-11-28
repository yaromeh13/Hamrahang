package ir.hamrahang.tv.network

import okhttp3.OkHttpClient
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {

    private const val BASE_URL = "https://hamrahang.ir/"

    private val client = OkHttpClient.Builder()
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(15, TimeUnit.SECONDS)
        .writeTimeout(15, TimeUnit.SECONDS)
        .retryOnConnectionFailure(true)
        .addInterceptor(NetworkInterceptor())
        .build()

    private fun createApi(client: OkHttpClient): ApiService =
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)

    // Startup Ping has one strict 15-second total deadline.
    // It does not retry internally; another attempt requires the user's Retry action.
    private val pingClient: OkHttpClient by lazy {
        client.newBuilder()
            .callTimeout(15, TimeUnit.SECONDS)
            .retryOnConnectionFailure(false)
            .build()
    }

    val api: ApiService by lazy {
        createApi(client)
    }

    val pingApi: ApiService by lazy {
        createApi(pingClient)
    }
}
