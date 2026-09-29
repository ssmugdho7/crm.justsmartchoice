package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.reminders.manage

import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.*
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.EditReminderDialogBinding
import com.divesh.perfex.databinding.FragmentLeadRemindersBinding
import com.divesh.perfex.leads.domain.adapters.LeadsReminderAdapter
import com.divesh.perfex.leads.domain.interfaces.LeadReminderInterface
import com.divesh.perfex.leads.domain.models.RemindersResponseModel
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import dagger.hilt.android.AndroidEntryPoint
import java.util.*

@AndroidEntryPoint class LeadRemindersFragment : Fragment(), LeadReminderInterface {
    private var _binding: FragmentLeadRemindersBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = LeadsReminderAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private lateinit var leadId: String
    private val viewModel: LeadRemindersViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLeadRemindersBinding.inflate(inflater, container, false)
        val view = binding.root

        leadId = arguments?.getString("id").toString()
        viewModel.refresh(leadId)

        progressBar = binding.progressBar

        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh(leadId)
            binding.swipeRefresh.isRefreshing = false
        }

        binding.addReminder.setOnClickListener {
            val addLeadReminderBottomSheet = AddLeadReminderBottomSheet(viewModel, leadId)
            parentFragmentManager.let {
                addLeadReminderBottomSheet.show(it, "addLeadReminderBottomSheet")
            }
        }
        observeViewModel()

        return view
    }

    private fun observeViewModel() {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                listAdapter.updateList(it)
                if (it.isEmpty()) lottieAnimation.visibility = View.VISIBLE
                else lottieAnimation.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null && isError != "null") {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(context, isError, Toast.LENGTH_SHORT).show() }
            }
        }

        viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                    binding.swipeRefresh.visibility = View.GONE
                } else {
                    progressBar.visibility = View.GONE
                    binding.swipeRefresh.visibility = View.VISIBLE
                }
            }
        }

        viewModel.reminderDeletedAtposition.observe(viewLifecycleOwner){
            if(it != null){
                viewModel.refresh(leadId)
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun deleteReminder(id: Int, position: Int) {
        Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
            if (isConfirmed) {
                viewModel.deleteReminder(id, position)
            }
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh(leadId)
    }

    override fun editReminder(reminder: RemindersResponseModel.Reminder) {
        val editLeadReminderBottomSheet = EditLeadReminderBottomSheet(viewModel, reminder)
        parentFragmentManager.let {
            editLeadReminderBottomSheet.show(it, "editLeadReminderBottomSheet")
        }
    }

    class EditLeadReminderBottomSheet(
        private val viewModel: LeadRemindersViewModel,
        private val reminder: RemindersResponseModel.Reminder
    ) : BottomSheetDialogFragment() {
        private lateinit var _binding: EditReminderDialogBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = EditReminderDialogBinding.inflate(inflater)
            val view = binding.root
            observeViewModel()

            viewModel.getAssignees()
            binding.description.editText?.setText(reminder.description)
            binding.date.text = reminder.date

            binding.close.setOnClickListener {
                dismiss()
            }

            binding.save.setOnClickListener {
                validateAndUpdateReminder()
            }
            context?.let { Extensions().transformIntoDateTimepicker(binding.date, it, Date()) }

            return view
        }

        private fun validateAndUpdateReminder() {
            var canSubmit = true
            val descriptionLayout = binding.description
            val dateLayout = binding.date
            val assignedLayout = binding.assigned
            when {
                descriptionLayout.editText?.text?.isEmpty() == true -> {
                    canSubmit = false
                    descriptionLayout.error = binding.root.context.getString(R.string.field_required)
                }
                dateLayout.text?.isEmpty() == true -> {
                    canSubmit = false
                    dateLayout.error = binding.root.context.getString(R.string.field_required)
                }
                assignedLayout.text?.isEmpty() == true -> {
                    canSubmit = false
                    binding.assignedParent.error = binding.root.context.getString(R.string.field_required)
                }
            }
            if (canSubmit) {
                viewModel.updateReminder(
                    if(binding.sendMail.isChecked) 1 else 0,
                    descriptionLayout.editText?.text.toString(),
                    dateLayout.text.toString(),
                    reminder.id
                )
            }
        }

        private fun observeViewModel() {
            viewModel.assigneeList.observe(viewLifecycleOwner) {
                if (it != null) {
                    val items = arrayListOf<String>()
                    for (staff in it) {
                        items.add("${staff.firstname} ${staff.lastname}")
                    }
                    val adapter = context?.let { it1 ->
                        ArrayAdapter(
                            it1, R.layout.list_item, items
                        )
                    }
                    binding.assigned.setAdapter(adapter)
                }
            }

            viewModel.loadError.observe(viewLifecycleOwner) { isError ->
                if (!isError.equals("") && isError != null && isError != "null") {
                    binding.progressBar.visibility = View.GONE
                    Toast.makeText(context, isError, Toast.LENGTH_SHORT).show()
                }
            }

            viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
                isLoading?.let {
                    if (isLoading) {
                        binding.progressBar.visibility = View.VISIBLE
                    } else {
                        binding.progressBar.visibility = View.GONE
                    }
                }
            }

            viewModel.leadReminderCreated.observe(viewLifecycleOwner) {
                if (it) {
                    dismiss()
                }
            }
        }
    }

    class AddLeadReminderBottomSheet(
        private val viewModel: LeadRemindersViewModel, private val leadId: String
    ) : BottomSheetDialogFragment(){
        private lateinit var _binding: EditReminderDialogBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = EditReminderDialogBinding.inflate(inflater)
            val view = binding.root
            observeViewModel()
            viewModel.getAssignees()

            binding.close.setOnClickListener {
                dismiss()
            }

            binding.save.setOnClickListener {
                validateAndUpdateReminder()
            }

            context?.let { Extensions().transformIntoDateTimepicker(binding.date, it, Date()) }

            return view
        }

        private fun validateAndUpdateReminder() {
            var canSubmit = true
            val descriptionLayout = binding.description
            val dateLayout = binding.date
            val assignedLayout = binding.assigned
            when {
                descriptionLayout.editText?.text?.isEmpty() == true -> {
                    canSubmit = false
                    descriptionLayout.error = binding.root.context.getString(R.string.field_required)
                }
                dateLayout.text?.isEmpty() == true -> {
                    canSubmit = false
                    dateLayout.error = binding.root.context.getString(R.string.field_required)
                }
                assignedLayout.text?.isEmpty() == true -> {
                    canSubmit = false
                    binding.assignedParent.error = binding.root.context.getString(R.string.field_required)
                }
            }
            if (canSubmit) {
                val sendMail = if(binding.sendMail.isChecked) 1 else 0
                viewModel.createReminder(
                    sendMail,
                    descriptionLayout.editText?.text.toString(),
                    dateLayout.text.toString(),
                    "lead",
                    leadId
                )
            }
        }

        private fun observeViewModel() {
            viewModel.loadError.observe(viewLifecycleOwner) { isError ->
                if (!isError.equals("") && isError != null && isError != "null") {
                    binding.progressBar.visibility = View.GONE
                    Toast.makeText(context, isError, Toast.LENGTH_SHORT).show()
                }
            }

            viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
                isLoading?.let {
                    if (isLoading) {
                        binding.progressBar.visibility = View.VISIBLE
                    } else {
                        binding.progressBar.visibility = View.GONE
                    }
                }
            }
            viewModel.assigneeList.observe(viewLifecycleOwner) {
                if (it != null) {
                    val items = arrayListOf<String>()
                    for (staff in it) {
                        items.add("${staff.firstname} ${staff.lastname}")
                    }
                    val adapter = context?.let { it1 ->
                        ArrayAdapter(
                            it1, R.layout.list_item, items
                        )
                    }
                    binding.assigned.setAdapter(adapter)
                }
            }

            viewModel.leadReminderCreated.observe(viewLifecycleOwner) {
                if (it) {
                    dismiss()
                }
            }
        }
    }
}