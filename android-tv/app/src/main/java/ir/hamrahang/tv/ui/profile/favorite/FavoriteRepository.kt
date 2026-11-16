package ir.hamrahang.tv.data.favorite

import ir.hamrahang.tv.network.ApiClient

class FavoriteRepository {

    suspend fun favorites(): FavoriteResponseDto {

        return ApiClient.api.getFavorites()

    }

}