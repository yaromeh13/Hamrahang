package ir.hamrahang.tv.util

import android.content.Context
import androidx.annotation.StringRes

object Ui {

    lateinit var app: Context

    fun init(context: Context) {
        app = context.applicationContext
    }
}

val @receiver:StringRes Int.str: String
    get() = Ui.app.getString(this)