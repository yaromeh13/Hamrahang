package ir.hamrahang.tv.data.local

import androidx.room.TypeConverter

class RoomConverters {

    @TypeConverter
    fun fromList(value: List<String>): String {

        return value.joinToString("|")

    }

    @TypeConverter
    fun toList(value: String): List<String> {

        if (value.isBlank())
            return emptyList()

        return value.split("|")

    }

}