package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.album.AlbumDto
import ir.hamrahang.tv.data.local.entity.AlbumEntity
import ir.hamrahang.tv.domain.album.Album

object AlbumEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type

    fun fromDto(dto: AlbumDto, isFull: Boolean) = AlbumEntity(
        id = dto.id, name = dto.name,
        names = gson.toJson(dto.names ?: emptyMap<String, String>()),
        artistIds = gson.toJson(dto.artists?.map { it.id } ?: emptyList<String>()),
        songIds = gson.toJson(dto.songs?.map { it.id } ?: emptyList<String>()),
        meta = gson.toJson(dto.meta), isFull = isFull, updatedAt = System.currentTimeMillis()
    )

    fun merge(old: AlbumEntity, dto: AlbumDto, isFull: Boolean): AlbumEntity {
        val oldMeta = jsonToMeta(old.meta)
        val newMeta = dto.meta
        val mergedMeta = if (newMeta == null) oldMeta else newMeta.copy(
            type = newMeta.type ?: oldMeta?.type, act = newMeta.act ?: oldMeta?.act,
            tmb = newMeta.tmb ?: oldMeta?.tmb, art = newMeta.art ?: oldMeta?.art,
            lrc = newMeta.lrc ?: oldMeta?.lrc, des = newMeta.des ?: oldMeta?.des,
            gnr = newMeta.gnr ?: oldMeta?.gnr, lan = newMeta.lan ?: oldMeta?.lan,
            rls = newMeta.rls ?: oldMeta?.rls, lik = newMeta.lik ?: oldMeta?.lik,
            ncp = newMeta.ncp ?: oldMeta?.ncp, dur = newMeta.dur ?: oldMeta?.dur,
            tmv = newMeta.tmv ?: oldMeta?.tmv, cvr = newMeta.cvr ?: oldMeta?.cvr,
            gal = newMeta.gal ?: oldMeta?.gal
        )
        return old.copy(
            name = dto.name ?: old.name,
            names = dto.names?.let { gson.toJson(it) } ?: old.names,
            artistIds = dto.artists?.let { gson.toJson(it.map { a -> a.id }) } ?: old.artistIds,
            songIds = dto.songs?.let { gson.toJson(it.map { s -> s.id }) } ?: old.songIds,
            meta = gson.toJson(mergedMeta), isFull = old.isFull || isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: AlbumEntity) = Album(
        id = entity.id, name = entity.name, names = jsonToNames(entity.names),
        artists = jsonToIds(entity.artistIds), songs = jsonToIds(entity.songIds),
        meta = jsonToMeta(entity.meta)
    )

    fun toDomainList(list: List<AlbumEntity>) = list.map(::toDomain)
    fun jsonToIds(json: String): List<String> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<String>>() {}.type)
    private fun jsonToNames(json: String): Map<String, String> =
        if (json.isBlank()) emptyMap() else gson.fromJson<Map<String, String>>(json, namesType)
    fun jsonToMeta(json: String): MediaMeta? =
        if (json.isBlank()) null else gson.fromJson(json, MediaMeta::class.java)
}
