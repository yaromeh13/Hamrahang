package ir.hamrahang.tv.data.live

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.LiveEntity

object LiveEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type
    private val sourcesType = object : TypeToken<List<MediaSource>>() {}.type

    fun fromDto(dto: LiveDto, position: Int, updatedAt: Long): LiveEntity? {
        val id = dto.id?.takeIf { it.isNotBlank() } ?: return null
        return LiveEntity(
            id = id,
            name = dto.name,
            names = gson.toJson(dto.names ?: emptyMap<String, String>()),
            meta = gson.toJson(dto.meta ?: MediaMeta(type = "live")),
            sources = gson.toJson(dto.sources ?: emptyList<MediaSource>()),
            position = position,
            updatedAt = updatedAt
        )
    }

    fun toDomain(entity: LiveEntity): LiveItem =
        LiveItem(
            id = entity.id,
            name = entity.name,
            names = runCatching {
                gson.fromJson<Map<String, String>>(entity.names, namesType)
            }.getOrNull() ?: emptyMap(),
            meta = runCatching {
                gson.fromJson(entity.meta, MediaMeta::class.java)
            }.getOrNull(),
            sources = runCatching {
                gson.fromJson<List<MediaSource>>(entity.sources, sourcesType)
            }.getOrNull() ?: emptyList()
        )
}
