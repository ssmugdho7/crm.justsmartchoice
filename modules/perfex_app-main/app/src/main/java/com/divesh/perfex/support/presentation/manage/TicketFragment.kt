package com.divesh.perfex.support.presentation.manage

import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.FrameLayout
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.fragments.CoreFragment
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentSupportBinding
import com.divesh.perfex.support.domain.adapters.SupportAdapter
import com.divesh.perfex.support.domain.interfaces.SupportInterface
import com.divesh.perfex.support.domain.models.SupportResponseModel
import com.divesh.perfex.support.presentation.add.AddTicketActivity
import com.divesh.perfex.support.presentation.edit.EditTicketActivity
import com.divesh.perfex.support.presentation.view_public_form.presentation.ViewPublicFormActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class TicketFragment : CoreFragment(), SupportInterface {
    private var _binding: FragmentSupportBinding? = null
    private val binding get() = _binding!!
    private val viewModel: TicketViewModel by viewModels()
    private val listAdapter = SupportAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentSupportBinding.inflate(inflater, container, false)
        val view = binding.root
        registerInternetConnectionReceiver()
        progressBar = binding.progressBar

        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }
        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh()
            binding.swipeRefresh.isRefreshing = false
        }
        observeViewModel(view)
        binding.addSupport.setOnClickListener {
            startActivity(Intent(activity, AddTicketActivity::class.java))
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
        if (hasInternet)
            viewModel.refresh()
        return view
    }

    private fun observeViewModel(view: FrameLayout) {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                listAdapter.updateList(it)
            }
        }

        viewModel.totalCount.observe(viewLifecycleOwner) { totalCount ->
            if (totalCount != null) {
                binding.totalRecords.text = getString(R.string.total_count, totalCount)
                val lottieAnimation = view.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (totalCount > 0) {
                    binding.swipeRefresh.visibility = View.VISIBLE
                    lottieAnimation.visibility = View.GONE
                } else {
                    lottieAnimation.visibility = View.VISIBLE
                    binding.swipeRefresh.visibility = View.GONE
                }
            }
        }

        viewModel.filteredCount.observe(viewLifecycleOwner) { filteredCount ->
            binding.filteredRecords.text = getString(R.string.total_filtered, filteredCount)
        }

        viewModel.loadError.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null && isError != "null") {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(it, isError.toString(), Toast.LENGTH_SHORT).show() }
            }
        }

        viewModel.ticketDeletedPosition.observe(viewLifecycleOwner) {
            listAdapter.notifyItemRemoved(it)
            viewModel.refresh()
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

    override fun onResume() {
        super.onResume()
        viewModel.refresh()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onEditBtnClicked(record: SupportResponseModel.Ticket) {
        startActivity(Intent(context, EditTicketActivity::class.java).apply {
            putExtra("ticket", record)
        })
    }

    override fun onDeleteBtnClicked(id: Int, position: Int) {
        if(context != null){
            Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
                if (isConfirmed) {
                    viewModel.deleteTicket(id, position)
                }
            }
        }
    }

    override fun onViewPublicFormClicked(record: SupportResponseModel.Ticket) {
        startActivity(Intent(context, ViewPublicFormActivity::class.java).apply {
            putExtra("ticket", record)
        })
    }
}