package ir.hamrahang.tv.data.owner

import com.google.gson.Gson
import ir.hamrahang.tv.data.OwnerMeta
import ir.hamrahang.tv.data.SourceOwner
import ir.hamrahang.tv.data.local.DatabaseProvider
import ir.hamrahang.tv.data.local.entity.OwnerEntity

class OwnerRepository {

    private val dao = DatabaseProvider.database().ownerDao()
    private val gson = Gson()

    suspend fun getOwner(id: Long): SourceOwner? =
        dao.get(id)?.toDomain()

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
