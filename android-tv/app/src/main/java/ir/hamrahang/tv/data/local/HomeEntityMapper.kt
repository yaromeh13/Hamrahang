package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.local.entity.HomeEntity

object HomeEntityMapper {

    private val gson = Gson()

    fun toEntity(
        id: String,
        itemIds: List<String>,
        updatedAt: Long = System.currentTimeMillis()
    ): HomeEntity {

        return HomeEntity(

            id = id,

            itemIds = gson.toJson(itemIds),

            updatedAt = updatedAt

        )

    }

    fun toIds(
        entity: HomeEntity
    ): List<String> {

        if (entity.itemIds.isBlank())
            return emptyList()

        val type = object : TypeToken<List<String>>() {}.type

        return gson.fromJson(entity.itemIds, type)

    }

}