package com.divesh.perfex.sales.presentation.proposals.view.proposal_discussion

import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.FragmentViewProposalItemDiscussionsBinding
import com.divesh.perfex.sales.domain.adapters.proposals.ProposalItemDiscussionsAdapter
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ViewProposalItemDiscussionsFragment : Fragment() {
    private var _binding: FragmentViewProposalItemDiscussionsBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = ProposalItemDiscussionsAdapter(arrayListOf())
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: ProposalItemDiscussionsViewModel by viewModels()
    private var proposal: ProposalsResponseModel.Proposal? = null
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentViewProposalItemDiscussionsBinding.inflate(inflater, container, false)
        val view = binding.root
        observeViewModel()
        initViews(view)
        return view
    }

    private fun initViews(view: View) {
        proposal = arguments?.getParcelable("proposal")
        progressBar = binding.progressBar
        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            if(proposal != null)
                viewModel.refresh(proposal!!.id)
            binding.swipeRefresh.isRefreshing = false
        }
    }

    private fun observeViewModel() {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                val lottieAnimation = view?.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (it.isEmpty()) {
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                } else {
                    listAdapter.setRecords(it)
                    binding.usersList.visibility = View.VISIBLE
                    lottieAnimation?.visibility = View.GONE
                }
            }
        }

        viewModel.loadError.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null) {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(it, isError.toString(), Toast.LENGTH_SHORT).show() }
            }
        }

        viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onResume() {
        super.onResume()
        if(proposal != null)
            viewModel.refresh(proposal!!.id)
    }
}