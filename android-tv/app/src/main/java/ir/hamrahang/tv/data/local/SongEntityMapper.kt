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

object SongEntityMapper {
    private val gson = Gson()
    private val namesType = object : TypeToken<Map<String, String>>() {}.type

    fun fromDto(dto: SongDto, isFull: Boolean) = SongEntity(
        id = dto.id ?: "",
        name = dto.name,
        names = gson.toJson(dto.names ?: emptyMap<String, String>()),
        artistIds = gson.toJson(dto.artists?.mapNotNull { it.id } ?: emptyList<String>()),
        parentIds = gson.toJson(dto.parents ?: emptyList<ParentMini>()),
        meta = gson.toJson(dto.meta),
        sources = gson.toJson(dto.sources ?: emptyList<MediaSource>()),
        isFull = isFull,
        updatedAt = System.currentTimeMillis()
    )

    fun merge(old: SongEntity, dto: SongDto, isFull: Boolean): SongEntity = old.copy(
        name = dto.name ?: old.name,
        names = if (dto.names != null) gson.toJson(dto.names) else old.names,
        artistIds = dto.artists?.let { gson.toJson(it.mapNotNull { a -> a.id }) } ?: old.artistIds,
        parentIds = dto.parents?.let { gson.toJson(it) } ?: old.parentIds,
        meta = gson.toJson(mergeMeta(jsonToMeta(old.meta), dto.meta)),
        sources = dto.sources?.let { gson.toJson(it) } ?: old.sources,
        isFull = old.isFull || isFull,
        updatedAt = System.currentTimeMillis()
    )

    fun toDomain(entity: SongEntity, artists: List<Artist> = emptyList()) = Song(
        id = entity.id,
        name = entity.name,
        names = jsonToNames(entity.names),
        artists = artists,
        parents = jsonToParents(entity.parentIds),
        meta = jsonToMeta(entity.meta),
        sources = jsonToSources(entity.sources)
    )

    fun toEntityList(list: List<SongDto>, isFull: Boolean) = list.map { fromDto(it, isFull) }
    fun toDomainList(list: List<SongEntity>) = list.map { toDomain(it) }

    fun jsonToIds(json: String): List<String> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<String>>() {}.type)

    fun jsonToParents(json: String): List<ParentMini> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<ParentMini>>() {}.type)

    fun jsonToNames(json: String): Map<String, String> =
        if (json.isBlank()) emptyMap() else gson.fromJson<Map<String, String>>(json, namesType)

    fun jsonToMeta(json: String): MediaMeta? =
        if (json.isBlank()) null else gson.fromJson(json, MediaMeta::class.java)

    fun jsonToSources(json: String): List<MediaSource> =
        if (json.isBlank()) emptyList() else gson.fromJson(json, object : TypeToken<List<MediaSource>>() {}.type)

    private fun mergeMeta(old: MediaMeta?, new: MediaMeta?): MediaMeta? {
        if (new == null) return old
        return new.copy(
            type = new.type ?: old?.type, act = new.act ?: old?.act,
            tmb = new.tmb ?: old?.tmb, art = new.art ?: old?.art,
            lrc = new.lrc ?: old?.lrc, des = new.des ?: old?.des,
            gnr = new.gnr ?: old?.gnr, lan = new.lan ?: old?.lan,
            rls = new.rls ?: old?.rls, lik = new.lik ?: old?.lik,
            ncp = new.ncp ?: old?.ncp, dur = new.dur ?: old?.dur,
            tmv = new.tmv ?: old?.tmv, cvr = new.cvr ?: old?.cvr,
            gal = new.gal ?: old?.gal
        )
    }
}
