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

    val api: ApiService by lazy {

        Retrofit.Builder()

            .baseUrl(BASE_URL)

            .client(client)

            .addConverterFactory(GsonConverterFactory.create())

            .build()

            .create(ApiService::class.java)
    }
}
