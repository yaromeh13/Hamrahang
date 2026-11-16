package ir.hamrahang.tv.util

import android.content.Context

object UiUtils {

    fun dp(

        context: Context,

        value: Int

    ): Int {

        return (

                value *

                        context.resources.displayMetrics.density

                ).toInt()

    }

}