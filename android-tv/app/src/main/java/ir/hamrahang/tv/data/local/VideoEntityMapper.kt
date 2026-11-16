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

    fun fromDto(dto: VideoDto, isFull: Boolean): VideoEntity {
        val id = dto.id ?: return emptyEntity()
        val meta = dto.meta ?: emptyVideoMeta()

        return VideoEntity(
            id = id,
            title = dto.name ?: dto.names?.get("en"),
            titleFa = dto.name_fa ?: dto.names?.get("fa"),
            artistIds = gson.toJson(dto.artists?.map { it.id } ?: emptyList<String>()),
            songIds = gson.toJson(dto.songs?.mapNotNull { it.id } ?: emptyList<String>()),
            meta = gson.toJson(meta.copy(type = meta.type ?: "video")),
            sources = gson.toJson(dto.sources ?: emptyList<MediaSource>()),
            isFull = isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun merge(old: VideoEntity, dto: VideoDto, isFull: Boolean): VideoEntity {
        val oldMeta = jsonToMeta(old.meta)
        val newMeta = dto.meta
        val mergedMeta = if (newMeta == null) oldMeta ?: emptyVideoMeta() else MediaMeta(
            type = newMeta.type ?: oldMeta?.type ?: "video",
            tmb = newMeta.tmb ?: oldMeta?.tmb,
            art = newMeta.art ?: oldMeta?.art,
            lrc = newMeta.lrc ?: oldMeta?.lrc,
            des = newMeta.des ?: oldMeta?.des,
            gnr = newMeta.gnr ?: oldMeta?.gnr,
            lan = newMeta.lan ?: oldMeta?.lan,
            rls = newMeta.rls ?: oldMeta?.rls,
            lik = newMeta.lik ?: oldMeta?.lik,
            ncp = newMeta.ncp ?: oldMeta?.ncp,
            dur = newMeta.dur ?: oldMeta?.dur,
            tmv = newMeta.tmv ?: oldMeta?.tmv,
            cvr = newMeta.cvr ?: oldMeta?.cvr,
            gal = newMeta.gal ?: oldMeta?.gal
        )

        return old.copy(
            title = dto.name ?: dto.names?.get("en") ?: old.title,
            titleFa = dto.name_fa ?: dto.names?.get("fa") ?: old.titleFa,
            artistIds = dto.artists?.let { gson.toJson(it.map { artist -> artist.id }) } ?: old.artistIds,
            songIds = dto.songs?.let { gson.toJson(it.mapNotNull { song -> song.id }) } ?: old.songIds,
            meta = gson.toJson(mergedMeta),
            sources = dto.sources?.let { gson.toJson(it) } ?: old.sources,
            isFull = old.isFull || isFull,
            updatedAt = System.currentTimeMillis()
        )
    }

    fun toDomain(entity: VideoEntity, artists: List<Artist>): Video =
        Video(
            id = entity.id,
            title = entity.title,
            titleFa = entity.titleFa,
            artists = artists,
            songs = jsonToIds(entity.songIds),
            meta = jsonToMeta(entity.meta),
            sources = jsonToSources(entity.sources)
        )

    fun jsonToIds(json: String): List<String> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<String>>() {}.type
        return gson.fromJson(json, type)
    }

    fun jsonToMeta(json: String): MediaMeta? =
        if (json.isBlank()) null else gson.fromJson(json, MediaMeta::class.java)

    fun jsonToSources(json: String): List<MediaSource> {
        if (json.isBlank()) return emptyList()
        val type = object : TypeToken<List<MediaSource>>() {}.type
        return gson.fromJson(json, type)
    }

    private fun emptyVideoMeta() = MediaMeta(
        type = "video",
        tmb = null,
        art = null,
        lrc = null,
        des = null,
        gnr = null,
        lan = null,
        rls = null,
        lik = null,
        ncp = null,
        dur = null
    )

    private fun emptyEntity() = VideoEntity(
        id = "",
        title = null,
        titleFa = null,
        artistIds = "[]",
        songIds = "[]",
        meta = gson.toJson(emptyVideoMeta()),
        sources = "[]",
        isFull = false,
        updatedAt = System.currentTimeMillis()
    )
}
