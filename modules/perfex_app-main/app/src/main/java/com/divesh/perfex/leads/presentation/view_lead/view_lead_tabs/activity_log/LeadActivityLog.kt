package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.activity_log

import android.content.res.Resources
import android.os.Bundle
import android.util.TypedValue
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.divesh.perfex.R
import com.divesh.perfex.databinding.AddLeadActivityLogBinding
import com.divesh.perfex.databinding.FragmentLeadActivityLogBinding
import com.divesh.perfex.leads.domain.adapters.LeadsActivityLogsAdapter
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.lriccardo.timelineview.TimelineDecorator
import com.divesh.perfex.core.helpers.Extensions
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class LeadActivityLog : Fragment() {
    private var _binding: FragmentLeadActivityLogBinding? = null
    private val binding get() = _binding!!
    private lateinit var leadId : String
    private val listAdapter = LeadsActivityLogsAdapter(arrayListOf())
    private lateinit var progressBar: ProgressBar
    private val viewModel: LeadsActivityLogsViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLeadActivityLogBinding.inflate(inflater, container, false)
        val view = binding.root
        leadId = arguments?.getString("id").toString()
        viewModel.refresh( arguments?.getString("id").toString())

        progressBar = binding.progressBar

        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context, LinearLayoutManager.VERTICAL, false)
            adapter = listAdapter
            val colorPrimary = TypedValue()
            val theme: Resources.Theme = activity?.theme!!
            theme.resolveAttribute(R.attr.colorPrimary, colorPrimary, true)

            addItemDecoration(
                TimelineDecorator(
                    position = TimelineDecorator.Position.Left,
                    indicatorColor = colorPrimary.data, lineColor = colorPrimary.data
                )
            )
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh(arguments?.getString("id").toString())
            binding.swipeRefresh.isRefreshing = false
        }
        observeViewModel()

        binding.addLog.setOnClickListener {
            val addLeadLogBottomSheet = AddLeadLogBottomSheet(viewModel, leadId)
            parentFragmentManager.let {
                addLeadLogBottomSheet.show(it, "addLeadLogBottomSheet")
            }
        }
        return view
    }

    private fun observeViewModel() {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            if(users != null && users.isNotEmpty()) {
                listAdapter.updateList(users)
                binding.animationView.visibility = View.GONE
                binding.usersList.visibility = View.VISIBLE
            }else{
                binding.animationView.visibility = View.VISIBLE
                binding.usersList.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null && isError != "null") {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(it,  isError.toString(), Toast.LENGTH_SHORT).show() }
            }
        }

        viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                } else {
                    progressBar.visibility = View.GONE
                    binding.usersList.visibility = View.VISIBLE
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
        viewModel.refresh(arguments?.getString("id").toString())
    }

    class AddLeadLogBottomSheet(
        private val viewModel: LeadsActivityLogsViewModel, private val leadId: String
    ) : BottomSheetDialogFragment(){
        private lateinit var _binding: AddLeadActivityLogBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View{
            _binding = AddLeadActivityLogBinding.inflate(inflater)
            binding.close.setOnClickListener {
                dismiss()
            }

            binding.save.setOnClickListener {
                validateAndAddLog()
            }
            viewModel.loadError.observe(viewLifecycleOwner){
                if(it != null) {
                    Extensions().showMessage(binding.root, it)
                    dismiss()
                    viewModel.refresh(leadId)
                }
            }
            viewModel.logAdded.observe(viewLifecycleOwner){
                if(it != null) {
                    dismiss()
                    viewModel.refresh(leadId)
                }
            }
            return binding.root
        }

        private fun validateAndAddLog() {
            if(!binding.description.text.isNullOrBlank()){
                viewModel.addActivityLog(
                    binding.description.text.toString(),
                    leadId
                )
            }else{
                Toast.makeText(context, "Description required", Toast.LENGTH_SHORT).show()
            }
        }
    }
}