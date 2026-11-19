package ir.hamrahang.tv.lyrics

import android.content.Context
import android.graphics.Typeface
import androidx.core.content.res.ResourcesCompat
import android.graphics.Color
import android.graphics.drawable.GradientDrawable
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

class LyricsAdapter(
    private val context: Context
) : RecyclerView.Adapter<LyricsAdapter.LineHolder>() {

    private var frame = LyricsFrame(emptyList(), -1, LyricsViewMode.NORMAL)
    private var preferences = LyricsPreferences()
    private var timed = false

    fun submit(
        newFrame: LyricsFrame,
        newPreferences: LyricsPreferences,
        hasTimedLines: Boolean
    ) {
        val oldFrame = frame
        val oldPreferences = preferences
        val oldTimed = timed
        frame = newFrame
        preferences = newPreferences
        timed = hasTimedLines
        if (oldFrame.lines != newFrame.lines ||
            oldFrame.viewMode != newFrame.viewMode ||
            oldPreferences != newPreferences ||
            oldTimed != hasTimedLines
        ) {
            notifyDataSetChanged()
        } else if (oldFrame.activeIndex != newFrame.activeIndex) {
            if (effectiveMode() == LyricsViewMode.LINE || effectiveMode() == LyricsViewMode.CARD) {
                notifyDataSetChanged()
            } else {
                if (oldFrame.activeIndex >= 0) notifyItemChanged(oldFrame.activeIndex)
                if (newFrame.activeIndex >= 0) notifyItemChanged(newFrame.activeIndex)
            }
        }
    }

    private fun effectiveMode(): LyricsViewMode =
        if (!timed) LyricsViewMode.NORMAL else frame.viewMode

    override fun getItemCount(): Int {
        val mode = effectiveMode()
        return if ((mode == LyricsViewMode.LINE || mode == LyricsViewMode.CARD) &&
            frame.activeIndex >= 0
        ) 1 else if (mode == LyricsViewMode.LINE || mode == LyricsViewMode.CARD) 0
        else frame.lines.size
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): LineHolder {
        val text = TextView(context).apply {
            layoutParams = RecyclerView.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
            gravity = Gravity.CENTER
            setPadding(16, 9, 16, 9)
            textDirection = View.TEXT_DIRECTION_FIRST_STRONG
            textAlignment = View.TEXT_ALIGNMENT_CENTER
            setTextColor(Color.WHITE)
        }
        return LineHolder(text)
    }

    override fun onBindViewHolder(holder: LineHolder, position: Int) {
        val mode = effectiveMode()
        val lineIndex = if (mode == LyricsViewMode.LINE || mode == LyricsViewMode.CARD) {
            frame.activeIndex
        } else position
        val line = frame.lines.getOrNull(lineIndex) ?: return
        val active = lineIndex == frame.activeIndex
        val text = holder.text
        text.text = line.text
        text.textSize = preferences.fontSize.toFloat()
        text.typeface = when (preferences.fontFamily) {
            "f1" -> ResourcesCompat.getFont(context, ir.hamrahang.tv.R.font.vazir) ?: Typeface.DEFAULT
            "f2" -> Typeface.SERIF
            "f3" -> Typeface.create("sans-serif", Typeface.NORMAL)
            "f4" -> Typeface.MONOSPACE
            else -> Typeface.DEFAULT
        }
        text.alpha = if (!timed) 1f else when {
            active -> 1f
            mode == LyricsViewMode.NORMAL && frame.activeIndex > lineIndex -> 0.42f
            mode == LyricsViewMode.FOCUS && kotlin.math.abs(frame.activeIndex - lineIndex) > 1 -> 0.55f
            else -> 1f
        }
        text.setTypeface(text.typeface, if (active) Typeface.BOLD else Typeface.NORMAL)
        text.background = if (active && mode == LyricsViewMode.CARD) {
            GradientDrawable().apply {
                setColor(0x33FFFFFF)
                cornerRadius = 14f
            }
        } else null
        text.minHeight = if (active) 56 else 44
    }

    class LineHolder(val text: TextView) : RecyclerView.ViewHolder(text)
}
