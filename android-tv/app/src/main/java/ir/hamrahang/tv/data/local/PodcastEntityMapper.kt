package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.PodcastEntity
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.podcast.Podcast

object PodcastEntityMapper {

    private val gson = Gson()

    fun fromDto(
        dto: SongDto,
        isFull: Boolean
    ): PodcastEntity {

        return PodcastEntity(
            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,

            artistIds = gson.toJson(
                dto.artists
                    ?.mapNotNull { it.id }
                    ?: emptyList<String>()
            ),

            parentIds = gson.toJson(
                dto.parents
                    ?.mapNotNull { it.id }
                    ?: emptyList<String>()
            ),

            meta = gson.toJson(dto.meta),

            sources = gson.toJson(
                dto.sources ?: emptyList<MediaSource>()
            ),

            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun merge(
        old: PodcastEntity,
        dto: SongDto,
        isFull: Boolean
    ): PodcastEntity {

        val oldMeta = jsonToMeta(old.meta)

        val mergedMeta =
            if (dto.meta == null) {
                oldMeta
            } else {
                MediaMeta(
                    type = dto.meta.type ?: oldMeta?.type,
                    tmb = dto.meta.tmb ?: oldMeta?.tmb,
                    art = dto.meta.art ?: oldMeta?.art,
                    lrc = dto.meta.lrc ?: oldMeta?.lrc,
                    des = dto.meta.des ?: oldMeta?.des,
                    gnr = dto.meta.gnr ?: oldMeta?.gnr,
                    lan = dto.meta.lan ?: oldMeta?.lan,
                    rls = dto.meta.rls ?: oldMeta?.rls,
                    lik = dto.meta.lik ?: oldMeta?.lik,
                    ncp = dto.meta.ncp ?: oldMeta?.ncp
                )
            }

        return old.copy(
            title = dto.name ?: old.title,
            titleFa = dto.name_fa ?: old.titleFa,

            artistIds =
                if (dto.artists != null) {
                    gson.toJson(
                        dto.artists.mapNotNull { it.id }
                    )
                } else {
                    old.artistIds
                },

            parentIds =
                if (dto.parents != null) {
                    gson.toJson(
                        dto.parents.mapNotNull { it.id }
                    )
                } else {
                    old.parentIds
                },

            meta = gson.toJson(mergedMeta),

            sources =
                if (dto.sources != null) {
                    gson.toJson(dto.sources)
                } else {
                    old.sources
                },

            isFull = old.isFull || isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(
        entity: PodcastEntity,
        artists: List<Artist> = emptyList()
    ): Podcast {

        return Podcast(
            id = entity.id,
            title = entity.title ?: "",
            titleFa = entity.titleFa,
            artists = artists,
            parents = jsonToParents(entity.parentIds),
            meta = jsonToMeta(entity.meta),
            sources = jsonToSources(entity.sources)
        )
    }

    fun jsonToIds(json: String): List<String> {
        if (json.isBlank()) return emptyList()

        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }

    private fun jsonToParents(json: String) =
        if (json.isBlank()) {
            emptyList<ir.hamrahang.tv.data.ParentMini>()
        } else {
            val type =
                object : TypeToken<List<ir.hamrahang.tv.data.ParentMini>>() {}.type
            gson.fromJson<List<ir.hamrahang.tv.data.ParentMini>>(json, type)
        }

    fun jsonToMeta(json: String): MediaMeta? {
        if (json.isBlank()) return null
        return gson.fromJson(json, MediaMeta::class.java)
    }

    fun jsonToSources(json: String): List<MediaSource> {
        if (json.isBlank()) return emptyList()

        val type = object : TypeToken<List<MediaSource>>() {}.type
        return gson.fromJson(json, type)
    }
}