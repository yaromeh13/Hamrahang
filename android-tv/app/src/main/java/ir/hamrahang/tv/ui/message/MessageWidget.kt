package ir.hamrahang.tv.ui.message

import android.view.View
import android.view.animation.OvershootInterpolator
import android.widget.ImageView
import android.widget.TextView
import androidx.lifecycle.LifecycleOwner
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.R
import kotlinx.coroutines.launch

class MessageWidget(

    root: View,

    owner: LifecycleOwner

) {

    private val container =
        root.findViewById<View>(R.id.messageRoot)

    private val icon =
        root.findViewById<ImageView>(R.id.imgIcon)

    private val text =
        root.findViewById<TextView>(R.id.txtMessage)

    init {

        owner.lifecycleScope.launch {

            MessageManager.state.collect {

                if (it.visible) {

                    show(it)

                } else {

                    hide()

                }

            }

        }

    }

    private fun show(

        state: MessageState

    ) {

        text.text = state.text

        when(state.type){

            MessageType.INFO ->

                icon.setImageResource(R.drawable.ic_info)

            MessageType.SUCCESS ->

                icon.setImageResource(R.drawable.ic_check)

            MessageType.WARNING ->

                icon.setImageResource(R.drawable.ic_warning)

            MessageType.ERROR ->

                icon.setImageResource(R.drawable.ic_error)

        }

        container.visibility = View.VISIBLE

        container.alpha = 0f

        container.translationY = -120f

        container.animate()

            .alpha(1f)

            .translationY(0f)

            .setDuration(350)

            .setInterpolator(

                OvershootInterpolator()

            )

            .start()

    }

    private fun hide() {

        if(container.visibility != View.VISIBLE)
            return

        container.animate()

            .alpha(0f)

            .translationY(-30f)

            .setDuration(220)

            .withEndAction {

                container.visibility = View.GONE

            }

            .start()

    }

}