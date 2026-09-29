package com.divesh.perfex.sales.presentation.invoices.presentation.manage

import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentManageInvoicesBinding
import com.divesh.perfex.sales.domain.adapters.invoices.ManageInvoicesAdapter
import com.divesh.perfex.sales.domain.interfaces.ManageInvoiceInterface
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse
import com.divesh.perfex.sales.presentation.invoices.presentation.add.AddInvoiceActivity
import com.divesh.perfex.sales.presentation.invoices.presentation.details.InvoiceDetailsActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ManageInvoicesFragment : Fragment(), ManageInvoiceInterface {
    private var _binding: FragmentManageInvoicesBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = ManageInvoicesAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: ManageInvoicesViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentManageInvoicesBinding.inflate(inflater, container, false)
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

        binding.addInvoicesFAB.setOnClickListener {
            val intent = Intent(binding.root.context, AddInvoiceActivity::class.java)
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
                    listAdapter.setRecords(it as List<InvoiceResponse.Invoice>)
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
    }


    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh(null, null)
    }

    override fun onInvoiceViewed(invoice: InvoiceResponse.Invoice) {
        val url = "${Constants.BASE_URL}/invoice/${invoice.id}/${invoice.hash}"
        context?.let { Extensions().openUrl(url, it) }
    }

    override fun onInvoiceEdit(invoice: InvoiceResponse.Invoice) {

    }

    override fun onInvoiceDetailsBtnClicked(invoice: InvoiceResponse.Invoice) {
        startActivity(Intent(context, InvoiceDetailsActivity::class.java).apply {
            putExtra("invoice",invoice)
        })
    }
}