package ir.hamrahang.tv.util

import ir.hamrahang.tv.domain.LocalizedTitle
import ir.hamrahang.tv.utils.AppState

fun LocalizedTitle.displayTitle(): String {

    return if (AppState.isPersian()) {

        titleFa ?: title ?: ""

    } else {

        title ?: ""

    }

}