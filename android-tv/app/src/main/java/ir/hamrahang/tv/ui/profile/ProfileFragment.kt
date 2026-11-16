package ir.hamrahang.tv.ui.profile

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.widget.FrameLayout
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.leanback.widget.HorizontalGridView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.ui.hero.HeroWidget
import ir.hamrahang.tv.ui.settings.SettingCategory
import ir.hamrahang.tv.ui.settings.SettingsCategoryAdapter
import ir.hamrahang.tv.ui.settings.SettingsOptionAdapter
import ir.hamrahang.tv.ui.settings.SettingOption

class ProfileFragment : Fragment(R.layout.fragment_profile) {
    private lateinit var categoryRecycler: HorizontalGridView
    private lateinit var contentContainer: FrameLayout
    private lateinit var categoryAdapter: SettingsCategoryAdapter
    private val categories = listOf(

        SettingCategory(
            "Profile",
            R.drawable.ic_profile
        ),

        SettingCategory(
            "Favorites",
            R.drawable.ic_favorite
        ),

        SettingCategory(
            "Playlists",
            R.drawable.ic_playlist
        ),

        SettingCategory(
            "Cleaning",
            R.drawable.ic_cleaning
        )

    )

    override fun onViewCreated(
        view: View,
        savedInstanceState: Bundle?
    ) {

        super.onViewCreated(view, savedInstanceState)

        categoryRecycler =
            view.findViewById(R.id.categoryRecycler)

        contentContainer =
            view.findViewById(R.id.contentContainer)

        categoryAdapter =
            SettingsCategoryAdapter(categories) {

                loadPage(it)

            }

        categoryRecycler.adapter = categoryAdapter

        loadPage(0)
    }

    private fun loadPage(index: Int) {

        contentContainer.removeAllViews()

        when (index) {

            0 -> {

                layoutInflater.inflate(
                    R.layout.view_profile_info,
                    contentContainer,
                    true
                )

                val hero =
                    HeroWidget(
                        contentContainer.findViewById(R.id.heroContainer),
                        viewLifecycleOwner
                    )

                val logout =
                    contentContainer.findViewById<View>(R.id.logoutButton)

                logout.setOnClickListener {

                    // TODO Logout

                }

            }

            1 -> {

                LayoutInflater.from(requireContext())
                    .inflate(
                        R.layout.view_profile_favorites,
                        contentContainer,
                        true
                    )

                /*
                 TODO

                 val rows =
                     contentContainer.findViewById<VerticalGridView>(R.id.rows)

                 rows.adapter =
                     ContentRowsAdapter(...)

                 */

            }

            2 -> {

                LayoutInflater.from(requireContext())
                    .inflate(
                        R.layout.view_profile_playlist,
                        contentContainer,
                        true
                    )

                val recycler =
                    contentContainer.findViewById<RecyclerView>(
                        R.id.optionsRecycler
                    )

                recycler.layoutManager =
                    LinearLayoutManager(requireContext())

                recycler.adapter =
                    SettingsOptionAdapter(

                        mutableListOf()

                    ) {

                        // Playlist click

                    }.apply {
                        submit(
                            listOf(
                                SettingOption("playlist_new", "➕ New Playlist"),
                                SettingOption("playlist_workout", "Workout"),
                                SettingOption("playlist_party", "Party"),
                                SettingOption("playlist_relax", "Relax")
                            )
                        )
                    }
            }

            3 -> {

                LayoutInflater.from(requireContext())
                    .inflate(
                        R.layout.view_profile_cleaning,
                        contentContainer,
                        true
                    )

                val recycler =
                    contentContainer.findViewById<RecyclerView>(
                        R.id.optionsRecycler
                    )

                recycler.layoutManager =
                    LinearLayoutManager(requireContext())

                recycler.adapter =
                    SettingsOptionAdapter(

                        mutableListOf()

                    ) {

                        // Cleaning action

                    }.apply {
                        submit(
                            listOf(
                                SettingOption("clean_recent", "Clear Recently Played"),
                                SettingOption("clean_search", "Clear Search History"),
                                SettingOption("clean_favorites", "Delete Favorites"),
                                SettingOption("clean_playlists", "Delete Playlists")
                            )
                        )
                    }
            }
        }
    }

}