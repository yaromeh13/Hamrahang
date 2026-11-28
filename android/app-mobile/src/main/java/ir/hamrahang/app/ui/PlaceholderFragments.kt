package ir.hamrahang.app.ui

import android.os.Bundle
import android.view.View
import android.widget.TextView
import androidx.annotation.StringRes
import androidx.fragment.app.Fragment
import ir.hamrahang.app.R

/** Tabs that are not built yet. Move each one to its own package when it gets real content. */
object PlaceholderFragments {

    abstract class Placeholder(@StringRes private val title: Int) :
        Fragment(R.layout.fragment_placeholder) {

        override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
            view.findViewById<TextView>(R.id.placeholderTitle).setText(title)
        }
    }

    class LiveFragment : Placeholder(R.string.nav_live)
    class SearchFragment : Placeholder(R.string.nav_search)
    class LibraryFragment : Placeholder(R.string.nav_library)
}
