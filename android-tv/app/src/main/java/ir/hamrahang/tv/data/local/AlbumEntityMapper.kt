package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.AlbumEntity
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.domain.album.Album

object AlbumEntityMapper {

    private val gson = Gson()

    //==================================================
    // DTO -> Entity
    //==================================================

    fun fromDto(
        dto: AlbumDto,
        isFull: Boolean
    ): AlbumEntity {

        return AlbumEntity(

            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            artistIds =
                gson.toJson(
                    dto.artists
                        ?.mapNotNull { it.id }
                        ?: listOf<String>()
                ),

            songIds =
                gson.toJson(
                    dto.songs
                        ?.mapNotNull { it.id }
                        ?: listOf<String>()
                ),

            meta =
                gson.toJson(
                    dto.meta
                ),

            isFull = isFull,
            updatedAt =
                System.currentTimeMillis()

        )

    }

    //==================================================
    // Merge
    //==================================================

    fun merge(
        old: AlbumEntity,
        dto: AlbumDto,
        isFull: Boolean
    ): AlbumEntity {

        val oldMeta = jsonToMeta(old.meta)
        val mergedMeta =
            if (dto.meta == null) {
                oldMeta
            } else {
                MediaMeta(
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
                if (dto.artists != null)
                    gson.toJson(
                        dto.artists.mapNotNull { it.id }
                    )
                else
                    old.artistIds,
            songIds =
                if (dto.songs != null)
                    gson.toJson(
                        dto.songs.mapNotNull { it.id }
                    )
                else
                    old.songIds,
            meta =
                gson.toJson(
                    mergedMeta
                ),
            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    // Entity -> Domain

    fun toDomain(
        entity: AlbumEntity
    ): Album {
        return Album(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            artists = jsonToIds(entity.artistIds),
            songs = jsonToIds(entity.songIds),
            meta = jsonToMeta(entity.meta)
        )
    }

    fun toDomainList(
        list: List<AlbumEntity>
    ) =
        list.map(::toDomain)

    //==================================================

    fun jsonToIds(
        json: String
    ): List<String> {

        if (json.isBlank())
            return emptyList()

        val type =
            object : TypeToken<List<String>>() {}.type

        return gson.fromJson(json, type)

    }

    fun jsonToMeta(
        json: String
    ): MediaMeta? {

        if (json.isBlank())
            return null

        return gson.fromJson(
            json,
            MediaMeta::class.java
        )

    }

}