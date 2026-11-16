package ir.hamrahang.tv.data.local

interface EntityUpdater<T> {

    suspend fun syncMini(dto: T)

    suspend fun syncFull(dto: T)

    suspend fun syncMini(list: List<T>) {

        list.forEach {

            syncMini(it)

        }

    }

    suspend fun syncFull(list: List<T>) {

        list.forEach {

            syncFull(it)

        }

    }

}