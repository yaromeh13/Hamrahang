package ir.hamrahang.tv.ui.message

import android.graphics.Color
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.view.animation.OvershootInterpolator
import android.widget.Button
import android.widget.FrameLayout
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.TextView
import androidx.core.content.ContextCompat
import androidx.lifecycle.LifecycleOwner
import androidx.lifecycle.lifecycleScope
import ir.hamrahang.tv.R
import kotlinx.coroutines.launch

class MessageWidget(root: View, owner: LifecycleOwner) {
    private val container = root.findViewById<LinearLayout>(R.id.messageRoot)
    private val icon = root.findViewById<ImageView>(R.id.imgIcon)
    private val text = root.findViewById<TextView>(R.id.txtMessage)
    private val actions = root.findViewById<LinearLayout>(R.id.messageActions)
    private val context = root.context

    init {
        owner.lifecycleScope.launch {
            MessageManager.state.collect { state ->
                if (state.visible) show(state) else hide()
            }
        }
    }

    private fun show(state: MessageState) {
        val isConfirmation = state.actions.isNotEmpty()
        text.text = state.text
        when (state.type) {
            MessageType.INFO -> icon.setImageResource(R.drawable.ic_info)
            MessageType.SUCCESS -> icon.setImageResource(R.drawable.ic_check)
            MessageType.WARNING -> icon.setImageResource(R.drawable.ic_warning)
            MessageType.ERROR -> icon.setImageResource(R.drawable.ic_error)
        }

        actions.removeAllViews()
        actions.visibility = if (isConfirmation) View.VISIBLE else View.GONE

        if (isConfirmation) {
            state.actions.forEachIndexed { index, action ->
                val button = Button(context).apply {
                    text = action.label
                    isAllCaps = false
                    isFocusable = true
                    isFocusableInTouchMode = true
                    setTextColor(Color.WHITE)
                    textSize = 17f
                    gravity = Gravity.CENTER
                    background = ContextCompat.getDrawable(context, R.drawable.bg_setting_option)
                    setCompoundDrawablesWithIntrinsicBounds(action.iconRes, 0, 0, 0)
                    compoundDrawablePadding = dp(10)
                    setPadding(dp(18), 0, dp(18), 0)
                    setOnClickListener { MessageManager.performAction(action) }
                }
                val params = LinearLayout.LayoutParams(0, dp(62), 1f)
                if (index > 0) params.marginStart = dp(14)
                actions.addView(button, params)
            }
            container.orientation = LinearLayout.VERTICAL
            container.background = ContextCompat.getDrawable(context, R.drawable.bg_setting_player)
            container.setPadding(dp(28), dp(24), dp(28), dp(22))
            icon.imageTintList = android.content.res.ColorStateList.valueOf(Color.WHITE)
            text.setTextColor(Color.WHITE)
            text.textSize = 20f
            text.gravity = Gravity.CENTER
            text.setPadding(dp(12), dp(4), dp(12), dp(22))
            val params = container.layoutParams as FrameLayout.LayoutParams
            params.width = (context.resources.displayMetrics.widthPixels * 0.72f).toInt()
            params.height = ViewGroup.LayoutParams.WRAP_CONTENT
            params.gravity = Gravity.CENTER
            params.topMargin = 0
            container.layoutParams = params
            state.actions.firstOrNull()?.let { first ->
                container.post {
                    if (container.visibility == View.VISIBLE) {
                        actions.getChildAt(0)?.requestFocus()
                    }
                }
            }
        } else {
            container.orientation = LinearLayout.HORIZONTAL
            container.background = ContextCompat.getDrawable(context, R.drawable.bg_message)
            container.setPadding(dp(18), 0, dp(22), 0)
            icon.imageTintList = android.content.res.ColorStateList.valueOf(0xFF202020.toInt())
            text.setTextColor(0xFF202020.toInt())
            text.textSize = 16f
            text.gravity = Gravity.CENTER_VERTICAL
            text.setPadding(0, 0, 0, 0)
            val params = container.layoutParams as FrameLayout.LayoutParams
            params.width = ViewGroup.LayoutParams.WRAP_CONTENT
            params.height = dp(56)
            params.gravity = Gravity.TOP or Gravity.CENTER_HORIZONTAL
            params.topMargin = dp(32)
            container.layoutParams = params
        }

        container.visibility = View.VISIBLE
        container.alpha = 0f
        container.translationY = if (isConfirmation) dp(24).toFloat() else -dp(120).toFloat()
        container.animate()
            .alpha(1f)
            .translationY(0f)
            .setDuration(300)
            .setInterpolator(OvershootInterpolator())
            .start()
    }

    private fun hide() {
        if (container.visibility != View.VISIBLE) return
        container.animate()
            .alpha(0f)
            .translationY(-dp(30).toFloat())
            .setDuration(180)
            .withEndAction { container.visibility = View.GONE }
            .start()
    }

    private fun dp(value: Int): Int =
        (value * context.resources.displayMetrics.density).toInt()
}
