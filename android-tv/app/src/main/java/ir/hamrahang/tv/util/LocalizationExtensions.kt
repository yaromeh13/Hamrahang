package ir.hamrahang.tv.util

import com.google.gson.JsonElement
import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.utils.AppState

fun LocalizedTitle.displayTitle(): String {
    val globalName = title?.trim()?.takeIf { it.isNotEmpty() }
    val localizedName = if (AppState.isPersian()) {
        titleFa?.trim()?.takeIf { it.isNotEmpty() }
    } else {
        null
    }

    return localizedName ?: globalName.orEmpty()
}

/** Reads a language-specific value from the API's names object. */
fun JsonElement?.localizedName(language: String): String? {
    val value = this
        ?.takeIf { it.isJsonObject }
        ?.asJsonObject
        ?.get(language.lowercase())
        ?.takeIf { it.isJsonPrimitive && it.asJsonPrimitive.isString }
        ?.asString
        ?.trim()

    return value?.takeIf { it.isNotEmpty() }
}