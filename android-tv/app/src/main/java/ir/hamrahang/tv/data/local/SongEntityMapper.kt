package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.ParentMini
import ir.hamrahang.tv.data.local.entity.SongEntity
import ir.hamrahang.tv.data.song.SongDto
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.song.Song
import ir.hamrahang.tv.util.UrlUtils

object SongEntityMapper {

    private val gson = Gson()

    //==================================================
    // DTO -> Entity
    //==================================================

    fun fromDto(
        dto: SongDto,
        isFull: Boolean
    ): SongEntity {

        return SongEntity(

            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            artistIds =
                gson.toJson(
                    dto.artists
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            parentIds =
                gson.toJson(
                    dto.parents
                        ?.mapNotNull {
                            val id = it.id ?: return@mapNotNull null
                            val type = it.type ?: return@mapNotNull null
                            ParentMini(
                                id = id,
                                type = type
                            )
                        }
                        ?: emptyList<ParentMini>()
                ),

            meta =
                gson.toJson(
                    dto.meta
                ),

            sources =
                gson.toJson(
                    dto.sources ?: listOf<MediaSource>()
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
        old: SongEntity,
        dto: SongDto,
        isFull: Boolean
    ): SongEntity {

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
            parentIds =
                if (dto.parents != null)
                    gson.toJson(
                        dto.parents.mapNotNull {
                            val id =
                                it.id ?: return@mapNotNull null
                            val type =
                                it.type ?: return@mapNotNull null
                            ParentMini(
                                id = id,
                                type = type
                            )
                        }
                    )
                else
                    old.parentIds,
            meta =
                gson.toJson(
                    mergedMeta
                ),
            sources =
                gson.toJson(
                    dto.sources ?: listOf<MediaSource>()
                ),
            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    //==================================================
    // Entity -> Domain
    //==================================================

    fun toDomain(
        entity: SongEntity,
        artists: List<Artist>
    ): Song {

        return Song(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            artists = artists,
            parents = jsonToParents(entity.parentIds),
            meta = jsonToMeta(entity.meta),
            sources = jsonToSources(entity.sources)
        )

    }

    fun toEntityList(
        list: List<SongDto>,
        isFull: Boolean
    ) =
        list.map {
            fromDto(
                dto = it,
                isFull = isFull
            )
        }

    fun toDomainList(
        list: List<SongEntity>
    ): List<Song> =
        list.map {
            toDomain(
                entity = it,
                artists = emptyList()
            )
        }

    //==================================================

    fun jsonToIds(
        json: String
    ): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }
    fun jsonToParents(
        json: String
    ): List<ParentMini> {

        if (json.isBlank())
            return emptyList()

        val type = object : TypeToken<List<ParentMini>>() {}.type

        return gson.fromJson(json, type)

    }
    fun jsonToMeta(
        json: String
    ): MediaMeta? {
        if (json.isBlank()) return null
        return gson.fromJson(
            json,
            MediaMeta::class.java
        )
    }

    fun jsonToSources(
        json: String
    ): List<MediaSource> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<MediaSource>>() {}.type
        return gson.fromJson(json, type)
    }
}


/*
package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.SongEntity
import ir.hamrahang.tv.domain.song.Song

object SongEntityMapper {

    private val gson = Gson()

    fun toEntity(song: Song): SongEntity {

        return SongEntity(

            id = song.id,
            title = song.title,
            titleFa = song.titleFa,

            //url = song.url,

            artistIds = gson.toJson(
                song.artists.mapNotNull { it.id }
            ),

            parentIds = gson.toJson(
                song.parents.mapNotNull { it.id }
            ),

            meta = gson.toJson(song.meta),
            sources = gson.toJson(song.sources),
            isFull = false,
            updatedAt = System.currentTimeMillis()

        )

    }

    fun toDomain(entity: SongEntity): Song {

        return Song(

            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            //url = entity.url,

            artists = emptyList(),
            parents = emptyList(),
            meta = jsonToMeta(entity.meta),
            sources = jsonToSources(entity.sources)

        )

    }

    fun merge(

        old: SongEntity,

        song: Song,

        isFull: Boolean = old.isFull

    ): SongEntity {

        return old.copy(

            title =
                if (song.title.isNullOrBlank())
                    old.title
                else
                    song.title,

            titleFa =
                if (song.titleFa.isNullOrBlank())
                    old.titleFa
                else
                    song.titleFa,

            //url = song.url ?: old.url,

            artistIds =
                if (song.artists.isNotEmpty())
                    gson.toJson(
                        song.artists.mapNotNull { it.id }
                    )
                else
                    old.artistIds,

            parentIds =
                if (song.parents.isNotEmpty())
                    gson.toJson(
                        song.parents.mapNotNull { it.id }
                    )
                else
                    old.parentIds,

            meta = gson.toJson(song.meta),
            sources = gson.toJson(song.sources),

            isFull = isFull,
            updatedAt = System.currentTimeMillis()

        )

    }

    fun toEntityList(
        list: List<Song>
    ) =
        list.map(::toEntity)

    fun toDomainList(
        list: List<SongEntity>
    ) =
        list.map(::toDomain)

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

    fun jsonToSources(
        json: String
    ): List<MediaSource> {

        if (json.isBlank())
            return emptyList()

        val type =
            object : TypeToken<List<MediaSource>>() {}.type

        return gson.fromJson(json, type)

    }

}*/