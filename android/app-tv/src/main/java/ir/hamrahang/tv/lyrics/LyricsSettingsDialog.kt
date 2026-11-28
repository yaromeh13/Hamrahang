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

        label("حالت نمایش")
        val modeGroup = radioGroup(
            listOf("NORMAL" to "عادی", "FOCUS" to "تمرکز", "LINE" to "خطی", "CARD" to "کارت"),
            current.viewMode.name
        )
        root.addView(modeGroup)

        val languages = (listOf("auto") + availableLanguages)
            .distinct()
            .map { code ->
                code to when (code) {
                    "auto" -> "خودکار"
                    "fa" -> "فارسی"
                    "en" -> "انگلیسی"
                    else -> code
                }
            }
        label("زبان")
        val languageSelection = if (languages.any { it.first == current.language }) current.language else "auto"
        val languageGroup = radioGroup(languages, languageSelection)
        root.addView(languageGroup)

        label("فونت")
        val fontGroup = radioGroup(
            listOf("" to "پیش‌فرض", "f1" to "وزیر", "f2" to "سریف", "f3" to "سن‌سریف", "f4" to "مونو"),
            current.fontFamily
        )
        root.addView(fontGroup)

        label("اندازه‌های آماده")
        val presets = radioGroup(
            listOf("13" to "13", "16" to "16", "20" to "20", "24" to "24"),
            if (current.fontSize in listOf(13, 16, 20, 24)) current.fontSize.toString() else ""
        )
        root.addView(presets)

        val sizeLabel = label("اندازه قلم: ${current.fontSize}sp")
        val sizeSeek = SeekBar(context).apply {
            max = 20
            progress = current.fontSize.coerceIn(12, 32) - 12
            isFocusable = true
        }
        presets.setOnCheckedChangeListener { group, checkedId ->
            val value = group.findViewById<RadioButton>(checkedId)
                ?.tag as? String
            value?.toIntOrNull()?.let { sizeSeek.progress = it - 12 }
        }
        sizeSeek.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(seekBar: SeekBar?, progress: Int, fromUser: Boolean) {
                sizeLabel.text = "اندازه قلم: ${progress + 12}sp"
                val presetId = (0 until presets.childCount)
                    .map { presets.getChildAt(it) as RadioButton }
                    .firstOrNull { (it.tag as? String)?.toIntOrNull() == progress + 12 }
                    ?.id ?: View.NO_ID
                if (presets.checkedRadioButtonId != presetId) {
                    if (presetId == View.NO_ID) presets.clearCheck() else presets.check(presetId)
                }
            }
            override fun onStartTrackingTouch(seekBar: SeekBar?) = Unit
            override fun onStopTrackingTouch(seekBar: SeekBar?) = Unit
        })
        root.addView(sizeSeek)

        AlertDialog.Builder(context)
            .setTitle("تنظیمات متن ترانه")
            .setView(root)
            .setNegativeButton("لغو", null)
            .setPositiveButton("اعمال") { _, _ ->
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
