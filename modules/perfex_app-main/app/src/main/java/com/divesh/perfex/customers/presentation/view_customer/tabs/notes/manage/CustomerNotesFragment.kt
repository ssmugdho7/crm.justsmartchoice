package com.divesh.perfex.customers.presentation.view_customer.tabs.notes.manage

import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.fragments.CoreFragment
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.domain.adapters.CustomerNotesAdapter
import com.divesh.perfex.customers.domain.interfaces.CustomerNotesInterface
import com.divesh.perfex.customers.domain.models.CustomerNotesResponseModel
import com.divesh.perfex.customers.presentation.view_customer.tabs.notes.add.AddCustomerNoteActivity
import com.divesh.perfex.customers.presentation.view_customer.tabs.notes.edit.EditNoteActivity
import com.divesh.perfex.databinding.FragmentCustomerNotesBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class CustomerNotesFragment : CoreFragment(), CustomerNotesInterface {
    private var _binding: FragmentCustomerNotesBinding? = null
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = CustomerNotesAdapter(arrayListOf(), this)
    private var userId = 0
    private val viewModel: CustomerNotesViewModel by viewModels()
    private val binding get() = _binding!!

    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentCustomerNotesBinding.inflate(inflater, container, false)
        val view = binding.root
        registerInternetConnectionReceiver()
        observeViewModel()
        checkArguments()
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
            viewModel.getCustomerNotes(userId)
            binding.swipeRefresh.isRefreshing = false
        }

        binding.addNotes.setOnClickListener {
            val intent = Intent(binding.root.context , AddCustomerNoteActivity::class.java).apply {
                putExtra("userId", userId)
            }
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

    private fun checkArguments() {
        userId = arguments?.getInt("userId", 0)!!
        if(userId > 0){
            viewModel.getCustomerNotes(userId)
        }else{
            Toast.makeText(context, "Customer not found!", Toast.LENGTH_SHORT).show()
        }
    }

    private fun observeViewModel() {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                val lottieAnimation = view?.findViewById<LottieAnimationView>(R.id.noDataFound)
                if(it.isEmpty()){
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                }else {
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

        viewModel.noteDeletedAtPosition.observe(viewLifecycleOwner){
            if(it != null){
                listAdapter.notifyItemRemoved(it)
                viewModel.getCustomerNotes(userId)
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onNoteDeleted(id: Int, position: Int) {
        Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
            if (isConfirmed) {
                viewModel.deleteCustomerNote(id, position)
            }
        }
    }

    override fun onEditBtnClicked(record: CustomerNotesResponseModel.Note) {
        val intent = Intent(binding.root.context , EditNoteActivity::class.java).apply {
            putExtra("note", record)
            putExtra("userId", userId)
        }
        startActivity(intent)
    }
}