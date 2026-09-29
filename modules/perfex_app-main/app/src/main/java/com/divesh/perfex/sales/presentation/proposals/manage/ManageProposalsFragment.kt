package com.divesh.perfex.sales.presentation.proposals.manage

import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
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
import com.divesh.perfex.databinding.FragmentManageProposalsBinding
import com.divesh.perfex.sales.domain.adapters.proposals.ManageProposalsAdapter
import com.divesh.perfex.sales.domain.interfaces.ManageProposalsInterface
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import com.divesh.perfex.sales.presentation.proposals.add.AddProposalActivity
import com.divesh.perfex.sales.presentation.proposals.view.ViewProposalActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ManageProposalsFragment : Fragment(), ManageProposalsInterface {
    private var _binding: FragmentManageProposalsBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = ManageProposalsAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: ManageProposalsViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentManageProposalsBinding.inflate(inflater, container, false)
        val view = binding.root
        observeViewModel()
        initViews(view)
        return view
    }

    private fun initViews(view: View) {
        progressBar = binding.progressBar
        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh(null, null)
            binding.swipeRefresh.isRefreshing = false
        }

        binding.addProposalsFAB.setOnClickListener {
            val intent = Intent(binding.root.context, AddProposalActivity::class.java)
            startActivity(intent)
        }
        binding.searchBar.addTextChangedListener(object : TextWatcher {
            override fun afterTextChanged(s: Editable?) {
            }

            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {
            }

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                viewModel.filterRecords(s.toString())
            }
        })
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

        viewModel.totalCount.observe(viewLifecycleOwner) { totalCount ->
            binding.totalRecords.text = getString(R.string.total_count, totalCount)
            if (totalCount != null) {
                val lottieAnimation = view?.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (totalCount > 0) {
                    binding.usersList.visibility = View.VISIBLE
                    lottieAnimation?.visibility = View.GONE
                } else {
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                }
            }
        }

        viewModel.filteredCount.observe(viewLifecycleOwner) { filteredCount ->
            binding.filteredRecords.text = getString(R.string.total_filtered, filteredCount)
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

        viewModel.proposalDeletedAtPosition.observe(viewLifecycleOwner) {
            if (it != null) {
                listAdapter.notifyItemRemoved(it)
                viewModel.refresh(null, null)
            }
        }
    }


    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh(null, null)
    }

    override fun onProposalClicked(proposal: ProposalsResponseModel.Proposal) {
        startActivity(Intent(context, ViewProposalActivity::class.java).apply {
            val bundle = Bundle()
            bundle.putParcelable("proposal",proposal)
            putExtra("myBundle",bundle)
            putExtra("proposal", proposal)
        })
    }
}