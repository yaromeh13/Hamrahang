package ir.hamrahang.tv.data.live

import com.google.gson.Gson
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.OwnerEntity
import ir.hamrahang.tv.network.ApiClient
import kotlinx.coroutines.CancellationException

class LiveRepository {
    private val liveDao = DatabaseProvider.database().liveDao()
    private val ownerDao = DatabaseProvider.database().ownerDao()
    private val gson = Gson()

    suspend fun getCached(): List<LiveItem> =
        liveDao.getAll().map(LiveEntityMapper::toDomain)

    /**
     * Search uses the same web search endpoint as the admin/web client.
     * Search results are upserted only; they must never evict unrelated cached lives.
     */
    suspend fun search(query: String): List<LiveItem> {
        val term = query.trim()
        if (term.isEmpty()) return emptyList()

        val response = ApiClient.api.searchLives(query = term)
        val cards = response.search.lives.orEmpty()
        val now = System.currentTimeMillis()

        // Only ask the full-card resolver for search results that are not full
        // and do not already have a complete local copy.
        val cardIds = cards.mapNotNull { it.id }
        val localById = if (cardIds.isEmpty()) {
            emptyMap()
        } else {
            liveDao.getByIds(cardIds).associateBy { it.id }
        }
        val needsResolve = cards.filter { dto ->
            val id = dto.id
            !dto.isFullCard && (id == null || localById[id]?.isFull != true)
        }.mapNotNull { it.id?.toLongOrNull() }.distinct()

        val resolvedById = if (needsResolve.isNotEmpty()) {
            try {
                ApiClient.api.resolveLives(
                    body = ResolveLivesRequest(needsResolve)
                ).associateBy { it.id }
            } catch (cancelled: CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                emptyMap()
            }
        } else {
            emptyMap()
        }

        val entities = cards.mapIndexedNotNull { index, searchDto ->
            val id = searchDto.id ?: return@mapIndexedNotNull null
            val local = localById[id] ?: liveDao.get(id)
            val dto = if (searchDto.isFullCard) {
                searchDto
            } else {
                resolvedById[id] ?: searchDto
            }
            syncOwner(dto, now)
            // Search ranking must not rewrite the default Live-page ordering.
            val cachePosition = local?.position ?: (Int.MAX_VALUE - 100 + index)
            LiveEntityMapper.merge(
                old = local,
                dto = dto,
                position = cachePosition,
                updatedAt = now,
                isFull = dto.isFullCard
            )
        }

        if (entities.isNotEmpty()) liveDao.upsertAll(entities)
        return getByIds(cards.mapNotNull { it.id })
    }

    suspend fun getByIds(ids: List<String>): List<LiveItem> {
        if (ids.isEmpty()) return emptyList()
        val cached = liveDao.getByIds(ids).associateBy { it.id }
        return ids.mapNotNull { id -> cached[id]?.let(LiveEntityMapper::toDomain) }
    }

    suspend fun refresh(): List<LiveItem> {
        val remote = ApiClient.api.getLatestLives()
        val now = System.currentTimeMillis()

        val entities = remote.mapIndexedNotNull { index, dto ->
            syncOwner(dto, now)
            LiveEntityMapper.merge(
                old = dto.id?.let { liveDao.get(it) },
                dto = dto,
                position = index,
                updatedAt = now,
                isFull = dto.isFullCard
            )
        }

        if (entities.isNotEmpty()) {
            liveDao.upsertAll(entities)
            liveDao.deleteExcept(entities.map { it.id })
        } else {
            // An empty server response is authoritative for the latest-list refresh only.
            liveDao.clear()
        }

        return getCached()
    }

    private suspend fun syncOwner(dto: LiveDto, now: Long) {
        val owners = buildList {
            dto.owner?.let(::add)
            dto.sources.orEmpty().mapNotNull { it.owner }.forEach(::add)
        }.distinctBy { it.id }

        owners.forEach { owner ->
            ownerDao.upsert(
                OwnerEntity(
                    id = owner.id,
                    name = owner.name?.takeIf { it.isNotBlank() }
                        ?: "User #${owner.id}",
                    meta = owner.meta?.let { gson.toJson(it) } ?: "{}",
                    updatedAt = now
                )
            )
        }
    }
}
