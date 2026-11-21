package ir.hamrahang.tv.data.auth

import android.content.Context
import android.os.Build
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import java.security.KeyStore
import java.security.SecureRandom
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

object AuthSessionStore {
    private const val PREFS = "hamrahang_auth_session"
    private const val KEY_ALIAS = "hamrahang_auth_key"

    private fun prefs(context: Context) =
        context.applicationContext.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    fun deviceId(context: Context): String {
        val p = prefs(context)
        p.getString("device_id", null)?.let { return it }
        val bytes = ByteArray(32)
        SecureRandom().nextBytes(bytes)
        val id = bytes.joinToString("") { "%02x".format(it) }
        p.edit().putString("device_id", id).apply()
        return id
    }

    fun save(context: Context, response: AuthResponse) {
        val p = prefs(context)
        val editor = p.edit()
        response.access_token?.let { editor.putString("access_token", protect(it)) }
        response.refresh_token?.let { editor.putString("refresh_token", protect(it)) }
        response.id_user?.let { editor.putString("user_id", it.toString()) }
        editor.putString("user_name", response.user_name.orEmpty())
        editor.putString("first_name", response.first_name.orEmpty())
        editor.putString("last_name", response.last_name.orEmpty())
        editor.putString("email", response.email.orEmpty())
        editor.putBoolean("logged_in", true)
        editor.apply()
    }

    fun accessToken(context: Context): String? =
        unprotect(prefs(context).getString("access_token", null))

    fun refreshToken(context: Context): String? =
        unprotect(prefs(context).getString("refresh_token", null))

    fun isLoggedIn(context: Context): Boolean =
        prefs(context).getBoolean("logged_in", false)

    fun displayName(context: Context): String {
        val p = prefs(context)
        val full = listOf(
            p.getString("first_name", "").orEmpty(),
            p.getString("last_name", "").orEmpty()
        ).filter { it.isNotBlank() }.joinToString(" ")
        return full.ifBlank {
            p.getString("user_name", "").orEmpty().ifBlank {
                p.getString("email", "").orEmpty()
            }
        }
    }

    fun clear(context: Context) {
        val p = prefs(context)
        val deviceId = p.getString("device_id", null)
        p.edit().clear().putString("device_id", deviceId).apply()
    }

    private fun protect(value: String): String {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.M) return "plain:$value"
        return runCatching {
            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.ENCRYPT_MODE, getOrCreateKey())
            val encrypted = cipher.doFinal(value.toByteArray(Charsets.UTF_8))
            "enc:${Base64.encodeToString(cipher.iv, Base64.NO_WRAP)}:${Base64.encodeToString(encrypted, Base64.NO_WRAP)}"
        }.getOrElse { "plain:$value" }
    }

    private fun unprotect(value: String?): String? {
        if (value == null) return null
        if (value.startsWith("plain:")) return value.removePrefix("plain:")
        if (!value.startsWith("enc:") || Build.VERSION.SDK_INT < Build.VERSION_CODES.M) return null
        return runCatching {
            val parts = value.split(":")
            val iv = Base64.decode(parts[1], Base64.NO_WRAP)
            val encrypted = Base64.decode(parts[2], Base64.NO_WRAP)
            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.DECRYPT_MODE, getOrCreateKey(), GCMParameterSpec(128, iv))
            String(cipher.doFinal(encrypted), Charsets.UTF_8)
        }.getOrNull()
    }

    private fun getOrCreateKey(): SecretKey {
        val keyStore = KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
        (keyStore.getKey(KEY_ALIAS, null) as? SecretKey)?.let { return it }
        val generator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore")
        generator.init(
            KeyGenParameterSpec.Builder(
                KEY_ALIAS,
                KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
            ).setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
                .setRandomizedEncryptionRequired(true)
                .build()
        )
        return generator.generateKey()
    }
}
