package com.divesh.perfex.tasks.presentation.view.tabs.checklist_items.manage

import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.AddCommentBottomSheetBinding
import com.divesh.perfex.databinding.AssignStaffBottomSheetBinding
import com.divesh.perfex.databinding.FragmentChecklistItemsBinding
import com.divesh.perfex.tasks.domain.adapters.CheckListItemsAdapter
import com.divesh.perfex.tasks.domain.interfaces.ChecklistItemInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import com.divesh.perfex.tasks.presentation.view.tabs.ViewTaskViewModel
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ChecklistItemsFragment : Fragment(), ChecklistItemInterface {
    private var _binding: FragmentChecklistItemsBinding? = null
    private val binding get() = _binding!!
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = CheckListItemsAdapter(arrayListOf(), this)
    private val viewModel: ViewTaskViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentChecklistItemsBinding.inflate(inflater, container, false)
        val view = binding.root
        observeViewModel()
        initViews()
        return view
    }

    private fun initViews() {
        lottieAnimation = binding.root.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
            binding.swipeRefresh.isRefreshing = false
        }
        binding.addChecklistBtn.setOnClickListener {
            viewModel.responseMessage.value = null
            viewModel.checklistItemAdded.value = null
            val addBottomSheet = AddBottomSheet(viewModel, arguments?.getString("id").toString())
            parentFragmentManager.let {
                addBottomSheet.show(it, "AddBottomSheet")
            }
        }
    }

    private fun observeViewModel() {
        viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
        viewModel.internetProblem.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root,  getString(R.string.no_internet))
            }
        }
        viewModel.responseMessage.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }
        viewModel.checklistItemAdded.observe(viewLifecycleOwner){
            if(it != null){
                if(it){
                    viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
                }
            }
        }
        viewModel.loading.observe(viewLifecycleOwner) {
            if (it) {
                binding.progressBar.visibility = View.VISIBLE
            } else {
                binding.progressBar.visibility = View.GONE
            }
        }
        viewModel.viewTaskResponse.observe(viewLifecycleOwner) {
            if (it != null) {
                if (it.task.checklist_items != null && it.task.checklist_items.isNotEmpty()) {
                    listAdapter.setRecords(
                        it.task.checklist_items,
                        it.staff,
                        arguments?.getString("id").toString()
                    )
                    binding.usersList.visibility = View.VISIBLE
                    lottieAnimation.visibility = View.GONE
                } else {
                    lottieAnimation.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                }
            } else {
                lottieAnimation.visibility = View.VISIBLE
                binding.usersList.visibility = View.GONE
            }
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
    }

    override fun onEdit(checklistItem: ViewTaskResponseModel.Task.ChecklistItems) {
        viewModel.responseMessage.value = null
        viewModel.checklistItemAdded.value = null
        val editBottomSheet = EditBottomSheet(viewModel, checklistItem)
        parentFragmentManager.let {
            editBottomSheet.show(it, "EditBottomSheet")
        }
    }

    override fun onAssignStaff(checklistItem: ViewTaskResponseModel.Task.ChecklistItems) {
        if(viewModel.viewTaskResponse.value?.staff?.isNotEmpty() == true){
            viewModel.responseMessage.value = null
            viewModel.checklistItemAdded.value = null
            val assignStaffBottomSheet = AssignStaffBottomSheet(viewModel, checklistItem,
                viewModel.viewTaskResponse.value?.staff!!
            )
            parentFragmentManager.let {
                assignStaffBottomSheet.show(it, "AssignStaffBottomSheet")
            }
        }else{
            Toast.makeText(context, getString(R.string.no_staff_available), Toast.LENGTH_SHORT).show()
        }
    }

    override fun onSaveAsTemplate(checklistItem: ViewTaskResponseModel.Task.ChecklistItems) {
        viewModel.saveAsTemplate(checklistItem.description)
    }

    override fun onCheckboxChecked(
        checklistItem: ViewTaskResponseModel.Task.ChecklistItems,
        isChecked: Boolean
    ) {
        viewModel.onChecklistItemToggled(checklistItem.id, isChecked)
    }

    override fun onDelete(checklistItem: ViewTaskResponseModel.Task.ChecklistItems) {
        Extensions().simpleAlert(
            getString(R.string.confirm_deletion),
            requireContext()
        ) { isConfirmed ->
            if (isConfirmed) {
                viewModel.removeChecklistItem(checklistItem.id, arguments?.getString("id").toString().toInt())
            }
        }
    }

    class AddBottomSheet(
        private val viewModel: ViewTaskViewModel,
        private val taskId: String
    ): BottomSheetDialogFragment() {
        private lateinit var _binding: AddCommentBottomSheetBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = AddCommentBottomSheetBinding.inflate(inflater)
            val view = binding.root
            initViews()
            observeViewModel()
            return view
        }

        private fun initViews() {
            binding.chooseFileBtn.visibility = View.GONE
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.save.setOnClickListener {
                if(binding.comment.editText?.text.toString().isNotBlank()){
                    viewModel.addCheckListItem(binding.comment.editText?.text.toString(), taskId)
                }else{
                    binding.comment.isErrorEnabled = true
                    binding.comment.error = getString(R.string.field_required)
                }
            }
        }

        private fun observeViewModel() {
            viewModel.checklistItemAdded.observe(viewLifecycleOwner) {
                if (it != null) {
                    if (it) {
                        Toast.makeText(context, getString(R.string.item_added), Toast.LENGTH_SHORT).show()
                        dismiss()
                    }
                }
            }
        }
    }

    class EditBottomSheet(
        private val viewModel: ViewTaskViewModel,
        private val items: ViewTaskResponseModel.Task.ChecklistItems
    ):  BottomSheetDialogFragment() {
        private lateinit var _binding: AddCommentBottomSheetBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = AddCommentBottomSheetBinding.inflate(inflater)
            val view = binding.root
            initViews()
            observeViewModel()
            return view
        }

        private fun initViews() {
            binding.chooseFileBtn.visibility = View.GONE
            binding.comment.editText?.setText(items.description)
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.save.setOnClickListener {
                if(binding.comment.editText?.text.toString().isNotBlank()){
                    viewModel.updateCheckListItem(binding.comment.editText?.text.toString(), items.id)
                }else{
                    binding.comment.isErrorEnabled = true
                    binding.comment.error = getString(R.string.field_required)
                }
            }
        }

        private fun observeViewModel() {
            viewModel.checklistItemAdded.observe(viewLifecycleOwner) {
                if (it != null) {
                    if (it) {
                        Toast.makeText(context, getString(R.string.item_added), Toast.LENGTH_SHORT).show()
                        dismiss()
                    }
                }
            }
        }
    }

    class AssignStaffBottomSheet(
        private val viewModel: ViewTaskViewModel,
        private val item: ViewTaskResponseModel.Task.ChecklistItems,
        private val staffList: List<ViewTaskResponseModel.Staff>
    ):  BottomSheetDialogFragment() {
        private lateinit var _binding: AssignStaffBottomSheetBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = AssignStaffBottomSheetBinding.inflate(inflater)
            val view = binding.root
            initViews()
            observeViewModel()
            return view
        }

        private fun initViews() {
            val items = arrayListOf<String>()
            for (staff in staffList) {
                items.add("${staff.firstname} ${staff.lastname}")
            }
            val adapter = context?.let { ArrayAdapter(it, R.layout.list_item, items) }
            binding.assignStaff.setAdapter(adapter)

            binding.close.setOnClickListener {
                dismiss()
            }
            binding.save.setOnClickListener {
                if(binding.assignStaff.text.toString().isNotBlank()){
                    var staffId = 0
                    for (staff in staffList) {
                        if("${staff.firstname} ${staff.lastname}" == binding.assignStaff.text.toString()){
                            staffId = staff.staffid
                            break
                        }
                    }
                    viewModel.assignStaffToChecklist(staffId, item.id, item.taskid)
                }else{
                    Toast.makeText(context, getString(R.string.select_staff), Toast.LENGTH_SHORT).show()
                }
            }
        }

        private fun observeViewModel() {
            viewModel.checklistItemAdded.observe(viewLifecycleOwner) {
                if (it != null) {
                    if (it) {
                        Toast.makeText(context, getString(R.string.item_added), Toast.LENGTH_SHORT).show()
                        dismiss()
                    }
                }
            }
        }
    }
}