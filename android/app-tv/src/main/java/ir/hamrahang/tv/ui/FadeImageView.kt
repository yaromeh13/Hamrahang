package ir.hamrahang.tv.ui.widget

import android.content.Context
import android.graphics.*
import android.util.AttributeSet
import androidx.appcompat.widget.AppCompatImageView
import ir.hamrahang.tv.utils.AppState

class FadeImageView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyle: Int = 0
) : AppCompatImageView(context, attrs, defStyle) {

    /**
     * 0..1 — چه کسری از عرض، سمت "متن" کاملاً opaque بماند
     * بقیه به سمت مخالف محو می‌شود
     */
    var fadeStart = 0.45f
        set(value) {
            field = value.coerceIn(0f, 1f)
            invalidate()
        }

    // نسبتی از ارتفاع که تا اونجا کاملاً opaque بمونه، بعدش به سمت پایین محو بشه
    var verticalFadeStart = 0.7f
        set(value) {
            field = value.coerceIn(0f, 1f)
            invalidate()
        }

    // اگه فید عمودی لازم نداری، این رو false کن
    var verticalFadeEnabled = true
        set(value) {
            field = value
            invalidate()
        }
    private val fadePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        xfermode = PorterDuffXfermode(PorterDuff.Mode.DST_IN)
    }

    override fun onDraw(canvas: Canvas) {

        val layer = canvas.saveLayer(0f, 0f, width.toFloat(), height.toFloat(), null)

        super.onDraw(canvas)

        // وقتی فارسیه: منو/متن سمت راسته -> سمت راست opaque، سمت چپ محو
        // وقتی انگلیسیه: منو/متن سمت چپه -> سمت چپ opaque، سمت راست محو
        // --- فید افقی ---
        val horizontalShader = if (AppState.isPersian()) {
            LinearGradient(
                0f, 0f, width.toFloat(), 0f,
                intArrayOf(Color.WHITE, Color.WHITE, Color.TRANSPARENT),
                floatArrayOf(0f, fadeStart, 1f),
                Shader.TileMode.CLAMP
            )
        } else {
            LinearGradient(
                width.toFloat(), 0f, 0f, 0f,
                intArrayOf(Color.WHITE, Color.WHITE, Color.TRANSPARENT),
                floatArrayOf(0f, fadeStart, 1f),
                Shader.TileMode.CLAMP
            )
        }

        fadePaint.shader = horizontalShader
        canvas.drawRect(0f, 0f, width.toFloat(), height.toFloat(), fadePaint)

        // --- فید عمودی از پایین ---
        if (verticalFadeEnabled) {
            val verticalShader = LinearGradient(
                0f, 0f, 0f, height.toFloat(),
                intArrayOf(Color.WHITE, Color.WHITE, Color.TRANSPARENT),
                floatArrayOf(0f, verticalFadeStart, 1f),
                Shader.TileMode.CLAMP
            )

            fadePaint.shader = verticalShader
            canvas.drawRect(0f, 0f, width.toFloat(), height.toFloat(), fadePaint)
        }
        canvas.restoreToCount(layer)
    }
}


/*
package ir.hamrahang.tv.ui.widget

import android.content.Context
import android.graphics.*
import android.util.AttributeSet
import android.view.View
import androidx.appcompat.widget.AppCompatImageView

class FadeImageView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyle: Int = 0
) : AppCompatImageView(context, attrs, defStyle) {

    enum class Direction {
        LEFT_TO_RIGHT,
        RIGHT_TO_LEFT
    }

    private val direction: Direction
        get() = if (resources.configuration.layoutDirection == View.LAYOUT_DIRECTION_RTL)
            Direction.RIGHT_TO_LEFT
        else
            Direction.LEFT_TO_RIGHT

    var fadeStart = 0.60f
        set(value) {
            field = value.coerceIn(0f, 1f)
            invalidate()
        }

    private val fadePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        xfermode = PorterDuffXfermode(PorterDuff.Mode.DST_IN)
    }

    override fun onDraw(canvas: Canvas) {

        val layer = canvas.saveLayer(0f, 0f, width.toFloat(), height.toFloat(), null)

        super.onDraw(canvas)

        val shader = when (direction) {
            Direction.LEFT_TO_RIGHT ->
                LinearGradient(
                    0f, 0f, width.toFloat(), 0f,
                    intArrayOf(Color.WHITE, Color.WHITE, Color.TRANSPARENT),
                    floatArrayOf(0f, fadeStart, 1f),
                    Shader.TileMode.CLAMP
                )

            Direction.RIGHT_TO_LEFT ->
                LinearGradient(
                    width.toFloat(), 0f, 0f, 0f,
                    intArrayOf(Color.WHITE, Color.WHITE, Color.TRANSPARENT),
                    floatArrayOf(0f, fadeStart, 1f),
                    Shader.TileMode.CLAMP
                )
        }

        fadePaint.shader = shader

        canvas.drawRect(0f, 0f, width.toFloat(), height.toFloat(), fadePaint)

        canvas.restoreToCount(layer)
    }
}

 */