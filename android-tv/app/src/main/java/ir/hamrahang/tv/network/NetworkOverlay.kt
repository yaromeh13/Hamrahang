package ir.hamrahang.tv.ui.network

import android.view.View
import android.widget.Button
import ir.hamrahang.tv.R

class NetworkOverlay(
    root: View,
    retry: () -> Unit
) {

    private val panel =
        root.findViewById<View>(R.id.networkOverlay)

    init {

        root.findViewById<Button>(R.id.btnRetry)
            .setOnClickListener {

                retry()

            }
    }

    fun show() {

        panel.visibility = View.VISIBLE

    }

    fun hide() {

        panel.visibility = View.GONE

    }
}