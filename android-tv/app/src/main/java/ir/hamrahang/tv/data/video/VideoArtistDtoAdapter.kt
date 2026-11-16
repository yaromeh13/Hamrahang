package ir.hamrahang.tv.data.video

import com.google.gson.JsonDeserializationContext
import com.google.gson.JsonDeserializer
import com.google.gson.JsonElement
import com.google.gson.JsonObject
import ir.hamrahang.tv.data.artist.ArtistMeta
import java.lang.reflect.Type

class VideoArtistDtoAdapter : JsonDeserializer<VideoArtistDto> {

    override fun deserialize(
        json: JsonElement,
        typeOfT: Type,
        context: JsonDeserializationContext
    ): VideoArtistDto {
        if (json.isJsonPrimitive) {
            return VideoArtistDto(id = json.asString)
        }

        if (!json.isJsonObject) {
            throw IllegalStateException("Invalid video artist reference")
        }

        val obj: JsonObject = json.asJsonObject
        val idElement = obj.get("id")
            ?: throw IllegalStateException("Video artist id is missing")

        val meta = obj.get("meta")
            ?.takeUnless { it.isJsonNull }
            ?.let { context.deserialize<ArtistMeta>(it, ArtistMeta::class.java) }

        return VideoArtistDto(
            id = idElement.asString,
            name = obj.get("name")?.takeUnless { it.isJsonNull }?.asString,
            name_fa = obj.get("name_fa")?.takeUnless { it.isJsonNull }?.asString,
            names = obj.get("names")?.takeUnless { it.isJsonNull }?.takeIf { it.isJsonObject }?.asJsonObject?.entrySet()?.associate { entry -> entry.key to entry.value.asString },
            meta = meta
        )
    }
}
