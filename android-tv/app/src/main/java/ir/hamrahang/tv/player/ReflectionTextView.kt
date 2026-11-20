package ir.hamrahang.tv.player

import android.content.Context
import android.graphics.Canvas
import android.graphics.LinearGradient
import android.graphics.Shader
import android.util.AttributeSet
import androidx.appcompat.widget.AppCompatTextView

/**
 * Draws a vertically mirrored copy of the title with a fading reflection,
 * matching the web player's title treatment without a WebView.
 */
class ReflectionTextView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = android.R.attr.textViewStyle
) : AppCompatTextView(context, attrs, defStyleAttr) {

    override fun onDraw(canvas: Canvas) {
        val h = height.toFloat().coerceAtLeast(1f)
        val oldShader = paint.shader
        paint.shader = LinearGradient(
            0f,
            0f,
            0f,
            h,
            intArrayOf(0x00FFFFFF, 0x66FFFFFF),
            floatArrayOf(0f, 1f),
            Shader.TileMode.CLAMP
        )

        val save = canvas.save()
        canvas.translate(0f, h)
        canvas.scale(1f, -1f)
        super.onDraw(canvas)
        canvas.restoreToCount(save)
        paint.shader = oldShader
    }
}
