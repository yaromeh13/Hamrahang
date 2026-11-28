package ir.hamrahang.app.ui.home

import android.os.Bundle
import android.view.View
import androidx.core.os.bundleOf
import androidx.core.view.doOnLayout
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.GridLayoutManager
import ir.hamrahang.app.R
import ir.hamrahang.app.databinding.FragmentSeeAllBinding
import ir.hamrahang.app.ui.player.PlaybackStarter
import kotlinx.coroutines.launch

/** All items of one Home section, as a grid. */
class SeeAllFragment : Fragment(R.layout.fragment_see_all) {

    private val viewModel: HomeViewModel by activityViewModels()

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        val binding = FragmentSeeAllBinding.bind(view)
        val sectionId = requireArguments().getString(ARG_SECTION_ID)
        binding.toolbar.setNavigationOnClickListener { parentFragmentManager.popBackStack() }

        binding.grid.doOnLayout {
            if (view.parent == null) return@doOnLayout
            viewLifecycleOwner.lifecycleScope.launch {
                viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                    var shown: HomeSection? = null
                    viewModel.data.collect { data ->
                        val section = data.toUi(requireContext())
                            .firstOrNull { it.id == sectionId } ?: return@collect
                        if (section == shown) return@collect
                        shown = section
                        show(binding, section)
                    }
                }
            }
        }
    }

    private fun show(binding: FragmentSeeAllBinding, section: HomeSection) {
        binding.toolbar.title = section.title
        val columns = CardMetrics.visibleCards(resources.configuration, section.style)
            .toInt().coerceAtLeast(1)
        val available =
            binding.grid.width - binding.grid.paddingLeft - binding.grid.paddingRight
        val state = binding.grid.layoutManager?.onSaveInstanceState()
        binding.grid.layoutManager = GridLayoutManager(requireContext(), columns)
        binding.grid.adapter = CardAdapter(section, available / columns) { clicked, index ->
            PlaybackStarter.open(requireContext(), clicked, index)
        }
        state?.let { binding.grid.layoutManager?.onRestoreInstanceState(it) }
    }

    companion object {
        private const val ARG_SECTION_ID = "section_id"

        fun newInstance(sectionId: String) = SeeAllFragment().apply {
            arguments = bundleOf(ARG_SECTION_ID to sectionId)
        }
    }
}
