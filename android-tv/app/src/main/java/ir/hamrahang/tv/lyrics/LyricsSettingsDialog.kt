package ir.hamrahang.tv.lyrics

import android.content.Context
import android.view.View
import android.widget.LinearLayout
import android.widget.RadioButton
import android.widget.RadioGroup
import android.widget.SeekBar
import android.widget.TextView
import androidx.appcompat.app.AlertDialog

object LyricsSettingsDialog {
    fun show(
        context: Context,
        current: LyricsPreferences,
        availableLanguages: List<String>,
        onApply: (LyricsPreferences) -> Unit
    ) {
        val root = LinearLayout(context).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(24, 8, 24, 8)
        }

        fun label(value: String): TextView {
            val view = TextView(context).apply {
                text = value
                textSize = 16f
                setTextColor(0xFFFFFFFF.toInt())
                setPadding(0, 14, 0, 6)
            }
            root.addView(view)
            return view
        }

        fun radioGroup(values: List<Pair<String, String>>, selected: String): RadioGroup {
            return RadioGroup(context).apply {
                orientation = RadioGroup.HORIZONTAL
                values.forEach { (key, title) ->
                    addView(RadioButton(context).apply {
                        id = View.generateViewId()
                        tag = key
                        text = title
                        textSize = 14f
                        setTextColor(0xFFFFFFFF.toInt())
                        isFocusable = true
                        isChecked = key == selected
                    })
                }
            }
        }

        label("Display mode")
        val modeGroup = radioGroup(
            listOf("NORMAL" to "Normal", "FOCUS" to "Focus", "LINE" to "Line", "CARD" to "Card"),
            current.viewMode.name
        )
        root.addView(modeGroup)

        val languages = (listOf("auto") + availableLanguages)
            .distinct()
            .map { code ->
                code to when (code) {
                    "auto" -> "Auto"
                    "fa" -> "فارسی"
                    "en" -> "English"
                    else -> code
                }
            }
        label("Language")
        val languageGroup = radioGroup(languages, current.language)
        root.addView(languageGroup)

        label("Font")
        val fontGroup = radioGroup(
            listOf("" to "Default", "f1" to "Vazirmatn", "f2" to "Serif", "f3" to "Sans", "f4" to "Mono"),
            current.fontFamily
        )
        root.addView(fontGroup)

        val sizeLabel = label("Font size: \${current.fontSize}sp")
        val sizeSeek = SeekBar(context).apply {
            max = 20
            progress = current.fontSize.coerceIn(12, 32) - 12
            isFocusable = true
        }
        sizeSeek.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(seekBar: SeekBar?, progress: Int, fromUser: Boolean) {
                sizeLabel.text = "Font size: \${progress + 12}sp"
            }
            override fun onStartTrackingTouch(seekBar: SeekBar?) = Unit
            override fun onStopTrackingTouch(seekBar: SeekBar?) = Unit
        })
        root.addView(sizeSeek)

        AlertDialog.Builder(context)
            .setTitle("Lyrics settings")
            .setView(root)
            .setNegativeButton("Cancel", null)
            .setPositiveButton("Apply") { _, _ ->
                fun selected(group: RadioGroup, fallback: String): String =
                    group.findViewById<RadioButton>(group.checkedRadioButtonId)
                        ?.tag as? String ?: fallback

                onApply(
                    current.copy(
                        language = selected(languageGroup, current.language),
                        fontFamily = selected(fontGroup, current.fontFamily),
                        fontSize = sizeSeek.progress + 12,
                        viewMode = runCatching {
                            LyricsViewMode.valueOf(selected(modeGroup, current.viewMode.name))
                        }.getOrDefault(current.viewMode)
                    )
                )
            }
            .show()
    }
}
