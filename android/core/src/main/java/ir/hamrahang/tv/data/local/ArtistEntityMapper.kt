package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.artist.ArtistDto
import ir.hamrahang.tv.data.artist.ArtistMeta
import ir.hamrahang.tv.data.local.entity.ArtistEntity
import ir.hamrahang.tv.domain.artist.Artist

object ArtistEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type

    fun fromDto(dto: ArtistDto, isFull: Boolean) = ArtistEntity(
        id = dto.id, name = dto.name,
        names = gson.toJson(dto.names ?: emptyMap<String, String>()),
        meta = gson.toJson(dto.meta),
        albumIds = gson.toJson(dto.albums?.map { it.id } ?: emptyList<String>()),
        songIds = gson.toJson(dto.songs?.map { it.id } ?: emptyList<String>()),
        musicvideoIds = gson.toJson(dto.musicvideos?.map { it.id } ?: emptyList<String>()),
        videoIds = gson.toJson(dto.videos?.map { it.id } ?: emptyList<String>()),
        podcastIds = gson.toJson(dto.podcasts?.map { it.id } ?: emptyList<String>()),
        isFull = isFull, updatedAt = System.currentTimeMillis()
    )

    fun merge(old: ArtistEntity, dto: ArtistDto, isFull: Boolean): ArtistEntity {
        val oldMeta = jsonToMeta(old.meta)
        val n = dto.meta
        val meta = if (n == null) oldMeta else n.copy(
            tmb = n.tmb ?: oldMeta?.tmb, art = n.art ?: oldMeta?.art,
            gal = n.gal ?: oldMeta?.gal, des = n.des ?: oldMeta?.des,
            gnr = n.gnr ?: oldMeta?.gnr, nat = n.nat ?: oldMeta?.nat,
            cc = n.cc ?: oldMeta?.cc, act = n.act ?: oldMeta?.act,
            flw = n.flw ?: oldMeta?.flw, ytm = n.ytm ?: oldMeta?.ytm,
            ins = n.ins ?: oldMeta?.ins, tlg = n.tlg ?: oldMeta?.tlg,
            bd = n.bd ?: oldMeta?.bd, tmv = n.tmv ?: oldMeta?.tmv,
            cvr = n.cvr ?: oldMeta?.cvr, nca = n.nca ?: oldMeta?.nca,
            ncs = n.ncs ?: oldMeta?.ncs, ncm = n.ncm ?: oldMeta?.ncm,
            ncv = n.ncv ?: oldMeta?.ncv, ncp = n.ncp ?: oldMeta?.ncp
        )
        return old.copy(
            name = dto.name ?: old.name,
            names = dto.names?.let { gson.toJson(it) } ?: old.names,
            meta = gson.toJson(meta),
            albumIds = dto.albums?.let { gson.toJson(it.map { x -> x.id }) } ?: old.albumIds,
            songIds = dto.songs?.let { gson.toJson(it.map { x -> x.id }) } ?: old.songIds,
            musicvideoIds = dto.musicvideos?.let { gson.toJson(it.map { x -> x.id }) } ?: old.musicvideoIds,
            videoIds = dto.videos?.let { gson.toJson(it.map { x -> x.id }) } ?: old.videoIds,
            podcastIds = dto.podcasts?.let { gson.toJson(it.map { x -> x.id }) } ?: old.podcastIds,
            isFull = old.isFull || isFull, updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: ArtistEntity) = Artist(
        id = entity.id, name = entity.name, names = jsonToNames(entity.names),
        meta = jsonToMeta(entity.meta)
    )

    fun toDomainList(list: List<ArtistEntity>) = list.map(::toDomain)

    fun toDetails(entity: ArtistEntity) = Artist(
        id = entity.id, name = entity.name, names = jsonToNames(entity.names),
        meta = jsonToMeta(entity.meta), albumIds = jsonToIds(entity.albumIds),
        songIds = jsonToIds(entity.songIds), musicVideoIds = jsonToIds(entity.musicvideoIds),
        videoIds = jsonToIds(entity.videoIds), podcastIds = jsonToIds(entity.podcastIds)
    )

    fun jsonToIds(json: String): List<String> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<String>>() {}.type)
    private fun jsonToNames(json: String): Map<String, String> =
        if (json.isBlank()) emptyMap() else gson.fromJson<Map<String, String>>(json, namesType)
    fun jsonToMeta(json: String): ArtistMeta? =
        if (json.isBlank()) null else gson.fromJson(json, ArtistMeta::class.java)
}
