package ir.hamrahang.tv.data.live

import com.google.gson.Gson
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.OwnerEntity
import ir.hamrahang.tv.network.ApiClient

class LiveRepository {
    private val liveDao = DatabaseProvider.database().liveDao()
    private val ownerDao = DatabaseProvider.database().ownerDao()
    private val gson = Gson()

    suspend fun getCached(): List<LiveItem> =
        liveDao.getAll().map(LiveEntityMapper::toDomain)

    suspend fun refresh(): List<LiveItem> {
        val remote = ApiClient.api.getLatestLives()
        val now = System.currentTimeMillis()

        val entities = remote.mapIndexedNotNull { index, dto ->
            dto.sources.orEmpty()
                .mapNotNull { it.owner }
                .distinctBy { it.id }
                .forEach { owner ->
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

            LiveEntityMapper.fromDto(dto, index, now)
        }

        if (entities.isNotEmpty()) {
            liveDao.upsertAll(entities)
            liveDao.deleteExcept(entities.map { it.id })
        } else {
            // An empty server response is authoritative; keep no stale live cards.
            liveDao.clear()
        }

        return getCached()
    }
}
