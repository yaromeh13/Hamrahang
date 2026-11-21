package ir.hamrahang.tv.data.auth

data class AuthRequest(
    val a: String,
    val username: String? = null,
    val password: String? = null,
    val did: String? = null,
    val device_type: String? = null,
    val code: String? = null,
    val refresh_token: String? = null
)

data class AuthResponse(
    val error: String? = null,
    val status: String? = null,
    val access_token: String? = null,
    val refresh_token: String? = null,
    val id_user: Long? = null,
    val user_name: String? = null,
    val first_name: String? = null,
    val last_name: String? = null,
    val email: String? = null,
    val photo: String? = null,
    val meta: Map<String, String>? = null,
    val act: Int? = null,
    val qr_base64: String? = null,
    val pair_url: String? = null
) {
    fun displayName(): String =
        listOfNotNull(first_name?.takeIf { it.isNotBlank() }, last_name?.takeIf { it.isNotBlank() })
            .joinToString(" ")
            .ifBlank { user_name ?: email.orEmpty() }
}

data class HistorySyncItem(
    val id: Long,
    val type: String,
    val at: Long
)
