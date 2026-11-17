package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.util.localizedName

object ArtistEntityMapper {

    private val gson = Gson()

    fun fromDto(dto: ArtistDto, isFull: Boolean): ArtistEntity {
        return ArtistEntity(
            id = dto.id,
            title = dto.name,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa"),
            meta = gson.toJson(dto.meta),
            albumIds = gson.toJson(dto.albums?.map { it.id } ?: emptyList<String>()),
            songIds = gson.toJson(dto.songs?.map { it.id } ?: emptyList<String>()),
            musicvideoIds = gson.toJson(dto.musicvideos?.map { it.id } ?: emptyList<String>()),
            videoIds = gson.toJson(dto.videos?.map { it.id } ?: emptyList<String>()),
            podcastIds = gson.toJson(dto.podcasts?.map { it.id } ?: emptyList<String>()),
            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun merge(old: ArtistEntity, dto: ArtistDto, isFull: Boolean): ArtistEntity {
        val oldMeta = jsonToMeta(old.meta)
        val mergedMeta = dto.meta?.let { new ->
            new.copy(
                tmb = new.tmb ?: oldMeta?.tmb,
                art = new.art ?: oldMeta?.art,
                gal = new.gal ?: oldMeta?.gal,
                des = new.des ?: oldMeta?.des,
                gnr = new.gnr ?: oldMeta?.gnr,
                nat = new.nat ?: oldMeta?.nat,
                cc = new.cc ?: oldMeta?.cc,
                act = new.act ?: oldMeta?.act,
                flw = new.flw ?: oldMeta?.flw,
                ytm = new.ytm ?: oldMeta?.ytm,
                ins = new.ins ?: oldMeta?.ins,
                tlg = new.tlg ?: oldMeta?.tlg,
                bd = new.bd ?: oldMeta?.bd,
                tmv = new.tmv ?: oldMeta?.tmv,
                cvr = new.cvr ?: oldMeta?.cvr,
                nca = new.nca ?: oldMeta?.nca,
                ncs = new.ncs ?: oldMeta?.ncs,
                ncm = new.ncm ?: oldMeta?.ncm,
                ncv = new.ncv ?: oldMeta?.ncv,
                ncp = new.ncp ?: oldMeta?.ncp
            )
        } ?: oldMeta

        return old.copy(
            title = dto.name ?: old.title,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa")
                ?: old.titleFa,
            meta = gson.toJson(mergedMeta),
            albumIds = dto.albums?.let { gson.toJson(it.map { item -> item.id }) } ?: old.albumIds,
            songIds = dto.songs?.let { gson.toJson(it.map { item -> item.id }) } ?: old.songIds,
            musicvideoIds = dto.musicvideos?.let { gson.toJson(it.map { item -> item.id }) } ?: old.musicvideoIds,
            videoIds = dto.videos?.let { gson.toJson(it.map { item -> item.id }) } ?: old.videoIds,
            podcastIds = dto.podcasts?.let { gson.toJson(it.map { item -> item.id }) } ?: old.podcastIds,
            isFull = old.isFull || isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: ArtistEntity): Artist {
        return Artist(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            meta = jsonToMeta(entity.meta)
        )
    }

    fun toDomainList(list: List<ArtistEntity>) = list.map(::toDomain)

    fun toDetails(entity: ArtistEntity): Artist {
        return Artist(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            meta = jsonToMeta(entity.meta),
            albumIds = jsonToIds(entity.albumIds),
            songIds = jsonToIds(entity.songIds),
            musicVideoIds = jsonToIds(entity.musicvideoIds),
            videoIds = jsonToIds(entity.videoIds),
            podcastIds = jsonToIds(entity.podcastIds)
        )
    }

    fun jsonToIds(json: String): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }

    fun jsonToMeta(json: String): ArtistMeta? {
        if (json.isBlank()) return null
        return gson.fromJson(json, ArtistMeta::class.java)
    }
}