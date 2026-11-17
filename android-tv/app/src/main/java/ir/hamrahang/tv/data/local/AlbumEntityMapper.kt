package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.local.entity.AlbumEntity
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.domain.album.Album
import ir.hamrahang.tv.util.localizedName

object AlbumEntityMapper {

    private val gson = Gson()

    fun fromDto(dto: AlbumDto, isFull: Boolean): AlbumEntity {
        return AlbumEntity(
            id = dto.id,
            title = dto.name,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa"),
            artistIds = gson.toJson(dto.artists?.map { it.id } ?: emptyList<String>()),
            songIds = gson.toJson(dto.songs?.map { it.id } ?: emptyList<String>()),
            meta = gson.toJson(dto.meta),
            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun merge(old: AlbumEntity, dto: AlbumDto, isFull: Boolean): AlbumEntity {
        val oldMeta = jsonToMeta(old.meta)
        val mergedMeta = dto.meta?.let { new ->
            new.copy(
                type = new.type ?: oldMeta?.type,
                act = new.act ?: oldMeta?.act,
                tmb = new.tmb ?: oldMeta?.tmb,
                art = new.art ?: oldMeta?.art,
                lrc = new.lrc ?: oldMeta?.lrc,
                des = new.des ?: oldMeta?.des,
                gnr = new.gnr ?: oldMeta?.gnr,
                lan = new.lan ?: oldMeta?.lan,
                rls = new.rls ?: oldMeta?.rls,
                lik = new.lik ?: oldMeta?.lik,
                ncp = new.ncp ?: oldMeta?.ncp,
                dur = new.dur ?: oldMeta?.dur,
                tmv = new.tmv ?: oldMeta?.tmv,
                cvr = new.cvr ?: oldMeta?.cvr,
                gal = new.gal ?: oldMeta?.gal
            )
        } ?: oldMeta

        return old.copy(
            title = dto.name ?: old.title,
            titleFa = dto.name_fa?.takeIf { it.isNotBlank() }
                ?: dto.names.localizedName("fa")
                ?: old.titleFa,
            artistIds = dto.artists?.let { gson.toJson(it.map { artist -> artist.id }) } ?: old.artistIds,
            songIds = dto.songs?.let { gson.toJson(it.map { song -> song.id }) } ?: old.songIds,
            meta = gson.toJson(mergedMeta),
            isFull = old.isFull || isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: AlbumEntity): Album {
        return Album(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            artists = jsonToIds(entity.artistIds),
            songs = jsonToIds(entity.songIds),
            meta = jsonToMeta(entity.meta)
        )
    }

    fun toDomainList(list: List<AlbumEntity>) = list.map(::toDomain)

    fun jsonToIds(json: String): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }

    fun jsonToMeta(json: String): MediaMeta? {
        if (json.isBlank()) return null
        return gson.fromJson(json, MediaMeta::class.java)
    }
}