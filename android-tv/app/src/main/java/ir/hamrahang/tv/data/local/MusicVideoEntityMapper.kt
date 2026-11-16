package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.MusicVideoEntity
import ir.hamrahang.tv.data.musicvideo.MusicVideoDto
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.musicvideo.MusicVideo


object MusicVideoEntityMapper {

    private val gson = Gson()

    //==================================================
    // DTO -> Entity
    //==================================================

    fun fromDto(
        dto: MusicVideoDto,
        isFull: Boolean
    ): MusicVideoEntity {

        return MusicVideoEntity(

            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            artistIds =
                gson.toJson(
                    dto.artists
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            songIds =
                gson.toJson(
                    dto.songs
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
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
        old: MusicVideoEntity,
        dto: MusicVideoDto,
        isFull: Boolean
    ): MusicVideoEntity {

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
        entity: MusicVideoEntity,
        artists: List<Artist>
    ): MusicVideo {

        return MusicVideo(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            artists = artists,
            songs = jsonToIds(entity.songIds),
            meta = jsonToMeta(entity.meta),
            sources = jsonToSources(entity.sources)

        )

    }

    fun toEntityList(
        list: List<MusicVideoDto>,
        isFull: Boolean
    ) =
        list.map {
            fromDto(
                dto = it,
                isFull = isFull
            )
        }

    /*fun toDomainList(
        list: List<MusicVideoEntity>
    ) =
        list.map(::toDomain)
*/
    //==================================================

    fun jsonToIds(
        json: String
    ): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
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

        if (json.isBlank())
            return emptyList()

        val type =
            object : TypeToken<List<MediaSource>>() {}.type

        return gson.fromJson(json, type)

    }

}

