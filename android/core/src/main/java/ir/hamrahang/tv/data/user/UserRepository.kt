package ir.hamrahang.tv.data.local.user

import ir.hamrahang.tv.data.local.dao.UserDao
import ir.hamrahang.tv.data.local.entity.UserEntity

class UserRepository(
    private val dao: UserDao
) {

    suspend fun create(
        user: UserEntity
    ): Long {

        return dao.insert(user)
    }

    suspend fun update(
        user: UserEntity
    ) {

        dao.update(user)
    }

    suspend fun get(
        id: Long
    ): UserEntity? {

        return dao.get(id)
    }

    suspend fun getByRemoteId(
        remoteId: String
    ): UserEntity? {

        return dao.getByRemoteId(remoteId)
    }

    suspend fun getLoggedInUser(): UserEntity? {

        return dao.getLoggedInUser()
    }

    suspend fun login(
        user: UserEntity
    ): Long {

        // فقط یک کاربر می‌تواند لاگین باشد
        dao.logoutAll()

        return dao.insert(
            user.copy(
                isLoggedIn = true,
                updatedAt = System.currentTimeMillis()
            )
        )
    }

    suspend fun logout() {

        dao.logoutAll()
    }

    suspend fun delete(
        id: Long
    ) {

        dao.delete(id)
    }

    suspend fun clearAll() {

        dao.clearAll()
    }
}