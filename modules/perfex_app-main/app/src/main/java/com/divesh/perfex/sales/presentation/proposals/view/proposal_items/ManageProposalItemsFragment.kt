package com.divesh.perfex.sales.presentation.proposals.view.proposal_items

import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.core.text.parseAsHtml
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.FragmentManageProposalItemsBinding
import com.divesh.perfex.sales.domain.adapters.proposals.ManageProposalItemsAdapter
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ManageProposalItemsFragment : Fragment() {
    private var _binding: FragmentManageProposalItemsBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = ManageProposalItemsAdapter(arrayListOf())
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: ProposalItemViewModel by viewModels()
    private var proposal: ProposalsResponseModel.Proposal? = null
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentManageProposalItemsBinding.inflate(inflater, container, false)
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
                if (it.items.isEmpty()) {
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                } else {
                    listAdapter.setRecords(it.items, it.taxes)
                    binding.usersList.visibility = View.VISIBLE
                    lottieAnimation?.visibility = View.GONE
                    setProposal()
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

    private fun setProposal() {
        if(proposal != null){
            binding.subTotalAmount.text = proposal!!.subtotal.toString()
            binding.discount.text = proposal!!.discount_total.toString()
            binding.tax.text = proposal!!.total_tax.toString()
            binding.adjustment.text = proposal!!.adjustment.toString()
            binding.total.text = proposal!!.total.toString()

            binding.companyTitle.text = proposal!!.name
            binding.companyAddress.text = proposal!!.address?.parseAsHtml()
            binding.companyEmail.text = proposal!!.email
            binding.companyPhone.text = proposal!!.phone
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