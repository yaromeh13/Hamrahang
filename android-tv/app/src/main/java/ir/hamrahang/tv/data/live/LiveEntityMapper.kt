package ir.hamrahang.tv.data.live

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.local.entity.LiveEntity

object LiveEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type
    private val sourcesType = object : TypeToken<List<MediaSource>>() {}.type
    private val genresType = object : TypeToken<List<LiveGenre>>() {}.type
    private val languagesType = object : TypeToken<List<String>>() {}.type

    fun fromDto(
        dto: LiveDto,
        position: Int,
        updatedAt: Long,
        isFull: Boolean = dto.isFullCard
    ): LiveEntity? {
        val id = dto.id?.takeIf { it.isNotBlank() } ?: return null
        val owner = dto.owner ?: dto.sources.orEmpty().firstNotNullOfOrNull { it.owner }
        return LiveEntity(
            id = id,
            name = dto.name,
            names = gson.toJson(dto.names ?: emptyMap<String, String>()),
            meta = gson.toJson(dto.meta ?: MediaMeta(type = "live")),
            sources = gson.toJson(dto.sources ?: emptyList<MediaSource>()),
            position = position,
            updatedAt = updatedAt,
            url = dto.url,
            genres = gson.toJson(dto.genres ?: emptyList<LiveGenre>()),
            languages = gson.toJson(dto.languages ?: emptyList<String>()),
            owner = owner?.let { gson.toJson(it) },
            isFull = isFull,
            mediaType = dto.mediaType?.takeIf { it.equals("video", true) }?.lowercase() ?: "audio"
        )
    }

    fun merge(
        old: LiveEntity?,
        dto: LiveDto,
        position: Int,
        updatedAt: Long,
        isFull: Boolean = dto.isFullCard
    ): LiveEntity? {
        val fresh = fromDto(dto, position, updatedAt, isFull) ?: return null
        if (old == null) return fresh
        if (old.isFull && !isFull) return old

        return fresh.copy(
            name = fresh.name?.takeIf { it.isNotBlank() } ?: old.name,
            names = if (!dto.names.isNullOrEmpty()) fresh.names else old.names,
            meta = if (dto.meta != null) fresh.meta else old.meta,
            sources = if (!dto.sources.isNullOrEmpty()) fresh.sources else old.sources,
            url = fresh.url?.takeIf { it.isNotBlank() } ?: old.url,
            genres = if (!dto.genres.isNullOrEmpty()) fresh.genres else old.genres,
            languages = if (!dto.languages.isNullOrEmpty()) fresh.languages else old.languages,
            owner = fresh.owner ?: old.owner,
            isFull = old.isFull || isFull,
            mediaType = dto.mediaType?.takeIf { it.isNotBlank() }?.let {
                if (it.equals("video", true)) "video" else "audio"
            } ?: old.mediaType
        )
    }

    fun toDomain(entity: LiveEntity): LiveItem {
        val sources = runCatching {
            gson.fromJson<List<MediaSource>>(entity.sources, sourcesType)
        }.getOrNull() ?: emptyList()
        val owner = runCatching {
            entity.owner?.let { gson.fromJson(it, SourceOwner::class.java) }
        }.getOrNull() ?: sources.firstNotNullOfOrNull { it.owner }

        return LiveItem(
            id = entity.id,
            name = entity.name,
            names = runCatching {
                gson.fromJson<Map<String, String>>(entity.names, namesType)
            }.getOrNull() ?: emptyMap(),
            meta = runCatching {
                gson.fromJson(entity.meta, MediaMeta::class.java)
            }.getOrNull(),
            sources = sources,
            url = entity.url,
            genres = runCatching {
                gson.fromJson<List<LiveGenre>>(entity.genres, genresType)
            }.getOrNull() ?: emptyList(),
            languages = runCatching {
                gson.fromJson<List<String>>(entity.languages, languagesType)
            }.getOrNull() ?: emptyList(),
            owner = owner,
            mediaType = entity.mediaType
        )
    }
}
