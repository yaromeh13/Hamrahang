package ir.hamrahang.app.ui.home

import android.os.Bundle
import android.view.View
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import ir.hamrahang.app.MainActivity
import ir.hamrahang.app.R
import ir.hamrahang.app.databinding.FragmentHomeBinding
import ir.hamrahang.app.ui.player.PlaybackStarter
import kotlinx.coroutines.launch

class HomeFragment : Fragment(R.layout.fragment_home) {

    private val viewModel: HomeViewModel by activityViewModels()

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        val binding = FragmentHomeBinding.bind(view)
        val adapter = SectionAdapter(
            onCardClick = { section, index ->
                PlaybackStarter.open(requireContext(), section, index)
            },
            onSeeAll = { section -> (activity as? MainActivity)?.openSeeAll(section.id) }
        )
        binding.sections.layoutManager = LinearLayoutManager(requireContext())
        binding.sections.adapter = adapter
        binding.retry.setOnClickListener { viewModel.refresh() }

        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.data.collect { data ->
                    adapter.submit(data.toUi(requireContext()))
                    binding.loading.isVisible = data.loading && data.isEmpty
                    binding.empty.isVisible = !data.loading && data.isEmpty
                }
            }
        }
    }
}
