package ir.hamrahang.tv.data.local

import com.google.gson.Gson
import com.google.gson.reflect.TypeToken
import ir.hamrahang.tv.data.MediaMeta
import ir.hamrahang.tv.data.MediaSource
import ir.hamrahang.tv.data.local.entity.VideoEntity
import ir.hamrahang.tv.data.video.VideoDto
import ir.hamrahang.tv.domain.artist.Artist
import ir.hamrahang.tv.domain.video.Video

object VideoEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type

    fun fromDto(dto: VideoDto, isFull: Boolean): VideoEntity {
        val id = dto.id ?: return emptyEntity()
        val meta = dto.meta ?: MediaMeta(type = "video")
        return VideoEntity(
            id = id, name = dto.name,
            names = gson.toJson(dto.names ?: emptyMap<String, String>()),
            artistIds = gson.toJson(dto.artists?.map { it.id } ?: emptyList<String>()),
            songIds = gson.toJson(dto.songs?.mapNotNull { it.id } ?: emptyList<String>()),
            meta = gson.toJson(meta.copy(type = meta.type ?: "video")),
            sources = gson.toJson(dto.sources ?: emptyList<MediaSource>()),
            isFull = isFull, updatedAt = System.currentTimeMillis()
        )
    }

    fun merge(old: VideoEntity, dto: VideoDto, isFull: Boolean): VideoEntity {
        val oldMeta = jsonToMeta(old.meta)
        val n = dto.meta
        val meta = if (n == null) oldMeta ?: MediaMeta(type = "video") else n.copy(
            type = n.type ?: oldMeta?.type ?: "video",
            tmb = n.tmb ?: oldMeta?.tmb, art = n.art ?: oldMeta?.art,
            lrc = n.lrc ?: oldMeta?.lrc, des = n.des ?: oldMeta?.des,
            gnr = n.gnr ?: oldMeta?.gnr, lan = n.lan ?: oldMeta?.lan,
            rls = n.rls ?: oldMeta?.rls, lik = n.lik ?: oldMeta?.lik,
            ncp = n.ncp ?: oldMeta?.ncp, dur = n.dur ?: oldMeta?.dur,
            tmv = n.tmv ?: oldMeta?.tmv, cvr = n.cvr ?: oldMeta?.cvr,
            gal = n.gal ?: oldMeta?.gal
        )
        return old.copy(
            name = dto.name ?: old.name,
            names = dto.names?.let { gson.toJson(it) } ?: old.names,
            artistIds = dto.artists?.let { gson.toJson(it.map { a -> a.id }) } ?: old.artistIds,
            songIds = dto.songs?.let { gson.toJson(it.mapNotNull { s -> s.id }) } ?: old.songIds,
            meta = gson.toJson(meta),
            sources = dto.sources?.let { gson.toJson(it) } ?: old.sources,
            isFull = old.isFull || isFull, updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: VideoEntity, artists: List<Artist>) = Video(
        id = entity.id, name = entity.name, names = jsonToNames(entity.names),
        artists = artists, songs = jsonToIds(entity.songIds),
        meta = jsonToMeta(entity.meta), sources = jsonToSources(entity.sources)
    )

    fun jsonToIds(json: String): List<String> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<String>>() {}.type)
    private fun jsonToNames(json: String): Map<String, String> =
        if (json.isBlank()) emptyMap() else gson.fromJson<Map<String, String>>(json, namesType)
    fun jsonToMeta(json: String): MediaMeta? =
        if (json.isBlank()) null else gson.fromJson(json, MediaMeta::class.java)
    fun jsonToSources(json: String): List<MediaSource> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<MediaSource>>() {}.type)

    private fun emptyEntity() = VideoEntity(
        id = "", name = null, names = "{}", artistIds = "[]", songIds = "[]",
        meta = gson.toJson(MediaMeta(type = "video")), sources = "[]",
        isFull = false, updatedAt = System.currentTimeMillis()
    )
}
