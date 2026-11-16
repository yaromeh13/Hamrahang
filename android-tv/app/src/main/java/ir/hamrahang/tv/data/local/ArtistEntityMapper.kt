package ir.hamrahang.tv.data.local

import com.google.gson.reflect.TypeToken
import com.google.gson.Gson
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist

object ArtistEntityMapper {

    private val gson =
        Gson()

    //==================================================
    // DTO -> Entity
    // Home / Search / Detail
    //==================================================

    fun fromDto(
        dto: ArtistDto,
        isFull: Boolean
    ): ArtistEntity {

        return ArtistEntity(

            id = dto.id?.toString()?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            // ---------- Basic ----------
            // url = dto.url,
            // ---------- Full Meta JSON ----------

            meta =
                gson.toJson(
                    dto.meta
                ),


            // ---------- Relations ----------

            albumIds =
                gson.toJson(
                    dto.albums
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            songIds =
                gson.toJson(
                    dto.songs
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            musicvideoIds =
                gson.toJson(
                    dto.musicvideos
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            videoIds =
                gson.toJson(
                    dto.videos
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            podcastIds =
                gson.toJson(
                    dto.podcasts
                        ?.mapNotNull { it.id }
                        ?: emptyList<String>()
                ),

            // ---------- Cache ----------

            isFull = isFull,
            updatedAt = System.currentTimeMillis()

        )

    }



    //==================================================
    // Merge Detail into existing Artist
    //==================================================

    fun merge(
        old: ArtistEntity,
        dto: ArtistDto,
        isFull: Boolean
    ): ArtistEntity {

        val oldMeta =
            jsonToMeta(old.meta)

        val mergedMeta =

            if (dto.meta == null) {

                oldMeta

            } else {
                ArtistMeta(
                    tmb = dto.meta.tmb ?: oldMeta?.tmb,
                    art = dto.meta.art ?: oldMeta?.art,
                    gal = dto.meta.gal ?: oldMeta?.gal,
                    des = dto.meta.des ?: oldMeta?.des,
                    gnr = dto.meta.gnr ?: oldMeta?.gnr,
                    nat = dto.meta.nat ?: oldMeta?.nat,
                    flw = dto.meta.flw ?: oldMeta?.flw,
                    ytm = dto.meta.ytm ?: oldMeta?.ytm,
                    ins = dto.meta.ins ?: oldMeta?.ins,
                    tlg = dto.meta.tlg ?: oldMeta?.tlg,
                    bd = dto.meta.bd ?: oldMeta?.bd,
                    nca = dto.meta.nca ?: oldMeta?.nca,
                    ncs = dto.meta.ncs ?: oldMeta?.ncs,
                    ncm = dto.meta.ncm ?: oldMeta?.ncm,
                    ncv = dto.meta.ncv ?: oldMeta?.ncv,
                    ncp = dto.meta.ncp ?: oldMeta?.ncp
                )

            }

        return old.copy(
            title =dto.name ?: old.title,
            titleFa =dto.name_fa ?: old.titleFa,
            //url = dto.url ?: old.url,

            meta = gson.toJson(mergedMeta),

            albumIds =
                if (dto.albums != null)
                    gson.toJson(
                        dto.albums.mapNotNull { it.id }
                    )
                else
                    old.albumIds,

            songIds =
                if (dto.songs != null)
                    gson.toJson(
                        dto.songs.mapNotNull { it.id }
                    )
                else
                    old.songIds,

            musicvideoIds =
                if (dto.musicvideos != null)
                    gson.toJson(
                        dto.musicvideos.mapNotNull { it.id }
                    )
                else
                    old.musicvideoIds,

            videoIds =
                if (dto.videos != null)
                    gson.toJson(
                        dto.videos.mapNotNull { it.id }
                    )
                else
                    old.videoIds,

            podcastIds =
                if (dto.podcasts != null)
                    gson.toJson(
                        dto.podcasts.mapNotNull { it.id }
                    )
                else
                    old.podcastIds,

            isFull = isFull,
            updatedAt = System.currentTimeMillis()

        )

    }

    //==================================================
    // Entity -> Home/Search Card
    //==================================================

    fun toDomain(
        entity: ArtistEntity
    ): Artist {


        return Artist(

            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            //url = entity.url,
            meta = jsonToMeta(entity.meta)

        )
    }

    fun toDomainList(
        list: List<ArtistEntity>
    ) =
        list.map(::toDomain)



    //==================================================
    // Entity -> Detail Page
    //==================================================

    fun toDetails(
        entity: ArtistEntity
    ): Artist {


        return Artist(

            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            //url = entity.url,
            meta = jsonToMeta(entity.meta),
            albumIds = jsonToIds(entity.albumIds),
            songIds = jsonToIds(entity.songIds),
            musicVideoIds = jsonToIds(entity.musicvideoIds),
            videoIds = jsonToIds(entity.videoIds),
            podcastIds = jsonToIds(entity.podcastIds),

        )

    }
    fun jsonToIds(
        json: String
    ): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }

    fun jsonToMeta(
        json: String
    ): ArtistMeta? {

        if (json.isBlank())
            return null

        return gson.fromJson(
            json,
            ArtistMeta::class.java
        )

    }

}


/*package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist

object ArtistEntityMapper {

    private val gson = Gson()

    // DTO -> Entity

    fun toEntity(
        dto: ArtistDto,
        isFull: Boolean = false
    ): ArtistEntity {

        return ArtistEntity(

            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            //url = dto.url,
            meta = gson.toJson(dto.meta),

            albumIds =
                dto.albums
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: "",

            songIds =
                dto.songs
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: "",

            musicvideoIds =
                dto.musicvideos
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: "",

            videoIds =
                dto.videos
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: "",

            podcastIds =
                dto.podcasts
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: "",

            isFull = isFull,
            updatedAt = System.currentTimeMillis(),
        )
    }

    // Merge

    fun merge(

        old: ArtistEntity,
        dto: ArtistDto,
        isFull: Boolean

    ): ArtistEntity {

        val oldMeta =

            old.meta
                .takeIf { it.isNotBlank() }
                ?.let {

                    gson.fromJson(

                        it,

                        ArtistMeta::class.java

                    )

                }

        val mergedMeta =

            mergeMeta(

                oldMeta,

                dto.meta

            )

        return old.copy(

            title = dto.name ?: old.title,
            titleFa = dto.name_fa ?: old.titleFa,
            //url = dto.url ?: old.url,
            meta = gson.toJson(mergedMeta),
            albumIds =
                dto.albums
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: old.albumIds,

            songIds =
                dto.songs
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: old.songIds,

            musicvideoIds =
                dto.musicvideos
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: old.musicvideoIds,

            videoIds =
                dto.videos
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: old.videoIds,

            podcastIds =
                dto.podcasts
                    ?.mapNotNull { it.id }
                    ?.joinToString(",")
                    ?: old.podcastIds,

            updatedAt =
                System.currentTimeMillis(),

            isFull =
                old.isFull || isFull

        )

    }

    //==================================================
    // Entity -> Domain
    //==================================================

    fun toDomain(

        entity: ArtistEntity

    ): Artist {

        return Artist(

            id = entity.id,

            title = entity.title,

            titleFa = entity.titleFa,

            //url = entity.url,

            meta =
                entity.meta
                    .takeIf { it.isNotBlank() }
                    ?.let {

                        gson.fromJson(

                            it,

                            ArtistMeta::class.java

                        )

                    }

        )

    }

    fun toDomainList(

        list: List<ArtistEntity>

    ) = list.map(::toDomain)

    //==================================================
    // Meta Merge
    //==================================================

    private fun mergeMeta(

        old: ArtistMeta?,

        new: ArtistMeta?

    ): ArtistMeta? {

        if (old == null) return new
        if (new == null) return old

        return ArtistMeta(

            tmb = new.tmb ?: old.tmb,
            art = new.art ?: old.art,
            gal = new.gal ?: old.gal,
            des = new.des ?: old.des,
            gnr = new.gnr ?: old.gnr,

            nat = new.nat ?: old.nat,
            flw = new.flw ?: old.flw,
            ytm = new.ytm ?: old.ytm,
            ins = new.ins ?: old.ins,
            tlg = new.tlg ?: old.tlg,
            bd = new.bd ?: old.bd,

            nca = new.nca ?: old.nca,
            ncs = new.ncs ?: old.ncs,
            ncm = new.ncm ?: old.ncm,
            ncv = new.ncv ?: old.ncv,
            ncp = new.ncp ?: old.ncp

        )

    }

}


package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist

object ArtistEntityMapper {

    private val gson = Gson()

    //====================================================
    // DTO -> Entity
    //====================================================

    fun fromDto(

        dto: ArtistDto,
        old: ArtistEntity? = null,
        isFull: Boolean = false

    ): ArtistEntity {

        val mergedMeta =

            mergeMeta(

                old?.meta?.let {
                    gson.fromJson(it, ArtistMeta::class.java)
                },

                dto.meta

            )

        return ArtistEntity(

            id = dto.id ?: "",
            title = dto.name,
            titleFa = dto.name_fa,
            url = dto.url,

            meta = gson.toJson(mergedMeta),

            albumIds =
                dto.albums
                    ?.mapNotNull { it.id }
                    ?: old?.albumIds
                    ?: emptyList(),

            songIds =
                dto.songs
                    ?.mapNotNull { it.id }
                    ?: old?.songIds
                    ?: emptyList(),

            musicvideoIds =
                dto.musicvideos
                    ?.mapNotNull { it.id }
                    ?: old?.musicvideoIds
                    ?: emptyList(),

            videoIds =
                dto.videos
                    ?.mapNotNull { it.id }
                    ?: old?.videoIds
                    ?: emptyList(),

            podcastIds =
                dto.podcasts
                    ?.mapNotNull { it.id }
                    ?: old?.podcastIds
                    ?: emptyList(),

            updatedAt = System.currentTimeMillis(),

            isFull = old?.isFull == true || isFull

        )
    }

    //====================================================
    // Entity -> Domain
    //====================================================

    fun toDomain(

        entity: ArtistEntity

    ): Artist {

        return Artist(

            id = entity.id,

            title = entity.title,

            titleFa = entity.titleFa,

            url = entity.url,

            meta = entity.meta?.let {

                gson.fromJson(

                    it,

                    ArtistMeta::class.java

                )

            }

        )
    }

    fun toDomainList(

        list: List<ArtistEntity>

    ) = list.map(::toDomain)

    //====================================================
    // Meta Merge
    //====================================================

    private fun mergeMeta(

        old: ArtistMeta?,

        new: ArtistMeta?

    ): ArtistMeta? {

        if (old == null) return new
        if (new == null) return old

        return ArtistMeta(

            tmb = new.tmb ?: old.tmb,
            art = new.art ?: old.art,
            gal = new.gal ?: old.gal,
            des = new.des ?: old.des,
            gnr = new.gnr ?: old.gnr,

            nat = new.nat ?: old.nat,
            flw = new.flw ?: old.flw,
            ytm = new.ytm ?: old.ytm,
            ins = new.ins ?: old.ins,
            tlg = new.tlg ?: old.tlg,
            bd = new.bd ?: old.bd,

            nca = new.nca ?: old.nca,
            ncs = new.ncs ?: old.ncs,
            ncm = new.ncm ?: old.ncm,
            ncv = new.ncv ?: old.ncv,
            ncp = new.ncp ?: old.ncp

        )
    }

}

*/

/*
package ir.hamrahang.tv.data.local

import ir.hamrahang.tv.data.artist.ArtistDetailsDto
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.UrlUtils
import ir.hamrahang.tv.utils.AppState

object ArtistEntityMapper {

    // =========================
    // Home -> Entity
    // =========================

    fun fromDto(dto: ArtistDto): ArtistEntity {

        return ArtistEntity(

            id = dto.id ?: "",

            title = dto.name ?: "",

            titleFa = dto.name_fa ?: "",

            thumbUrl =
                UrlUtils.thumbUrl(dto.thumb),

            artistUrl =
                dto.artUrl
                    ?.firstOrNull()
                    ?.url
                    ,
            updatedAt = System.currentTimeMillis(),

            isFull = false

        )

    }

    // =========================
    // Detail -> Entity
    // =========================

    fun merge(

        old: ArtistEntity,

        dto: ArtistDetailsDto

    ): ArtistEntity {

        return old.copy(

            birthDate = dto.bd,

            instagram = dto.ins,

            telegram = dto.tlg,

            albumIds =
                dto.albums
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            songIds =
                dto.songs
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            videoIds =
                dto.videos
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            podcastIds =
                dto.podcast
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            updatedAt = System.currentTimeMillis(),

            isFull = true

        )

    }

    // =========================
    // Entity -> Domain
    // =========================

    fun toDomain(

        entity: ArtistEntity

    ): Artist {

        return Artist(

            id = entity.id,

            title = entity.title,

            titleFa = entity.titleFa,

            tmb = entity.thumbUrl,

            artistUrl = entity.artistUrl

        )

    }

    fun toDomainList(

        list: List<ArtistEntity>

    ) = list.map(::toDomain)

    fun toEntityList(

        list: List<ArtistDto>

    ) = list.map(::fromDto)

    fun fromDetails(

        dto: ArtistDetailsDto

    ): ArtistEntity {

        val isFa = AppState.isPersian()

        return ArtistEntity(

            id = dto.id?.toString() ?: "",

            title = dto.name ?: "",

            titleFa = dto.name_fa  ?: "",

            thumbUrl = UrlUtils.thumbUrl(dto.thumb),

            artistUrl = null,

            birthDate = dto.bd,

            instagram = dto.ins,

            telegram = dto.tlg,

            albumIds =
                dto.albums
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            songIds =
                dto.songs
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            videoIds =
                dto.videos
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            podcastIds =
                dto.podcast
                    ?.mapNotNull { it.id }
                    ?: emptyList(),

            updatedAt =
                System.currentTimeMillis(),

            isFull = true

        )

    }
}*/