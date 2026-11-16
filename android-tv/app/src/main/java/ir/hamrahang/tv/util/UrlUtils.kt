package ir.hamrahang.tv.util

object UrlUtils {

    fun thumbUrl(thumb: String?): String {
        if (thumb.isNullOrEmpty()) return ""
        return "https://hamrahang.ir/thumb/${thumb}.jpg"
    }
}