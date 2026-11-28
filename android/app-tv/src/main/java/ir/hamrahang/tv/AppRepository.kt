package ir.hamrahang.tv

import ir.hamrahang.tv.network.ApiService

class AppRepository(
    private val api: ApiService
) {
    suspend fun ping() = api.ping()
}