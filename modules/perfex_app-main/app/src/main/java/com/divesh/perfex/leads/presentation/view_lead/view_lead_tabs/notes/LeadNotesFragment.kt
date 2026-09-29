package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.notes

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
import com.divesh.perfex.databinding.AddNoteDialogBinding
import com.divesh.perfex.databinding.EditNotesDialogBinding
import com.divesh.perfex.databinding.FragmentLeadNotesBinding
import com.divesh.perfex.leads.domain.adapters.LeadNotesAdapter
import com.divesh.perfex.leads.domain.interfaces.LeadNotesInterface
import com.divesh.perfex.leads.domain.models.LeadNotes
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class LeadNotesFragment : Fragment(), LeadNotesInterface {
    private var _binding: FragmentLeadNotesBinding? = null
    private val binding get() = _binding!!
    private val viewModel: LeadNotesViewModel by viewModels()
    private val listAdapter = LeadNotesAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private lateinit var leadId : String

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLeadNotesBinding.inflate(inflater, container, false)
        val view = binding.root
        leadId = arguments?.getString("id").toString()
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

        binding.addNotes.setOnClickListener {
            val addLeadNoteBottomSheet = AddLeadNoteBottomSheet(viewModel, leadId)
            parentFragmentManager.let {
                addLeadNoteBottomSheet.show(it, "addLeadNoteBottomSheet")
            }
        }
        observeViewModel()
        viewModel.refresh(leadId)
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
                context?.let { Toast.makeText(it, isError.toString(), Toast.LENGTH_SHORT).show() }
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

        viewModel.noteDeletedPosition.observe(viewLifecycleOwner){
            listAdapter.notifyItemRemoved(it)
            viewModel.refresh(leadId)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun deleteNote(id: Int, position: Int) {
        Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
            if (isConfirmed) {
                viewModel.deleteNotes(
                    id,
                    position
                )
            }
        }
    }

    override fun editNote(note: LeadNotes.Note) {
        val editLeadNoteBottomSheet = EditLeadNoteBottomSheet(viewModel, note, leadId)
        parentFragmentManager.let {
            editLeadNoteBottomSheet.show(it, "editLeadNoteBottomSheet")
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh(leadId)
    }

    class EditLeadNoteBottomSheet(
        private val viewModel: LeadNotesViewModel,
        private val note: LeadNotes.Note,
        private val leadId: String
    ): BottomSheetDialogFragment(){
        private lateinit var _binding: EditNotesDialogBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = EditNotesDialogBinding.inflate(inflater)
            binding.description.setText(note.description)
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.save.setOnClickListener {
                validateAndUpdateNotes()
            }
            viewModel.loadError.observe(viewLifecycleOwner){
                if(it != null) {
                    Extensions().showMessage(binding.root, it)
                    dismiss()
                    viewModel.refresh(leadId)
                }
            }
            return binding.root
        }

        private fun validateAndUpdateNotes() {
            if(!binding.description.text.isNullOrBlank()){
                viewModel.updateLeadNote(note.description.toString(), note.id)
            }else{
                Toast.makeText(context, "Description required", Toast.LENGTH_SHORT).show()
            }
        }
    }

    class AddLeadNoteBottomSheet(
        private val viewModel: LeadNotesViewModel,
        private val leadId: String
    ): BottomSheetDialogFragment(){
        private lateinit var _binding: AddNoteDialogBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View{
            _binding = AddNoteDialogBinding.inflate(inflater)
            binding.date.visibility = View.GONE
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.lastContact.setOnCheckedChangeListener{_ , isChecked ->
                if(isChecked){
                    binding.date.visibility = View.VISIBLE
                }else{
                    binding.date.visibility = View.GONE
                }
            }
            binding.save.setOnClickListener {
                validateAndAddNotes()
            }
            viewModel.loadError.observe(viewLifecycleOwner){
                if(it != null) {
                    Extensions().showMessage(binding.root, it)
                    dismiss()
                    viewModel.refresh(leadId)
                }
            }
            return binding.root
        }

        private fun validateAndAddNotes() {
            if(!binding.description.text.isNullOrBlank()){
                viewModel.addNotes(
                    binding.description.text.toString(),
                    binding.date.text.toString(),
                    "lead",
                    leadId
                )
            }else{
                Toast.makeText(context, "Description required", Toast.LENGTH_SHORT).show()
            }
        }
    }
}