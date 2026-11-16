package ir.hamrahang.tv.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(
    tableName = "users"
)
data class UserEntity(

    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,

    // =========================================
    // Remote / Account Identity
    // =========================================

    val remoteId: String? = null,

    val username: String? = null,

    val email: String? = null,

    // =========================================
    // Profile
    // =========================================

    val name: String? = null,

    val avatar: String? = null,

    // =========================================
    // Account State
    // =========================================

    val isLoggedIn: Boolean = false,

    val isVerified: Boolean = false,

    // =========================================
    // Sync
    // =========================================

    val lastSync: Long? = null,

    // =========================================
    // Timestamps
    // =========================================

    val createdAt: Long = System.currentTimeMillis(),

    val updatedAt: Long = System.currentTimeMillis()

)