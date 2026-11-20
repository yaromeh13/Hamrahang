package ir.hamrahang.tv.data.owner

import com.google.gson.Gson
import ir.hamrahang.tv.data.OwnerMeta
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.OwnerEntity
import ir.hamrahang.tv.network.ApiClient

class OwnerRepository {

    companion object {
        // Match the web owner's in-memory cache: do not repeatedly resolve a
        // profile that was successfully checked but has no icon/thumbnail.
        private val checkedWithoutThumb = mutableSetOf<Long>()
    }

    private val dao = DatabaseProvider.database().ownerDao()
    private val gson = Gson()

    suspend fun getOwner(id: Long): SourceOwner? =
        dao.get(id)?.toDomain()

    suspend fun resolveByIds(ids: List<Long>): Map<Long, SourceOwner> {
        val validIds = ids.filter { it > 0L }.distinct()
        if (validIds.isEmpty()) return emptyMap()

        val cached = dao.getByIds(validIds).associateBy { it.id }
        val needsDetails = validIds.filter { id ->
            val meta = cached[id]?.meta
                ?.takeIf { it.isNotBlank() && it != "null" }
                ?.let { runCatching { gson.fromJson(it, OwnerMeta::class.java) }.getOrNull() }
            meta?.ico.isNullOrBlank() &&
                meta?.tmb.isNullOrBlank() &&
                id !in checkedWithoutThumb
        }

        if (needsDetails.isNotEmpty()) {
            try {
                ApiClient.api.getOwners(ids = needsDetails.joinToString(","))
                    .owners
                    .forEach { owner ->
                        dao.upsert(
                            OwnerEntity(
                                id = owner.id,
                                name = owner.name?.takeIf { it.isNotBlank() }
                                    ?: "User #${owner.id}",
                                meta = owner.meta?.let { gson.toJson(it) } ?: "{}",
                                updatedAt = System.currentTimeMillis()
                            )
                        )
                    }
                checkedWithoutThumb.addAll(needsDetails)
            } catch (cancelled: kotlinx.coroutines.CancellationException) {
                throw cancelled
            } catch (_: Exception) {
                // A failed request may be retried the next time the panel opens.
            }
        }

        return getByIds(validIds)
    }

    suspend fun getByIds(ids: List<Long>): Map<Long, SourceOwner> {
        if (ids.isEmpty()) return emptyMap()
        return dao.getByIds(ids.distinct()).associate { entity ->
            entity.id to entity.toDomain()
        }
    }

    private fun OwnerEntity.toDomain(): SourceOwner =
        SourceOwner(
            id = id,
            name = name,
            meta = meta.takeIf { it.isNotBlank() && it != "null" }
                ?.let { gson.fromJson(it, OwnerMeta::class.java) }
        )
}
