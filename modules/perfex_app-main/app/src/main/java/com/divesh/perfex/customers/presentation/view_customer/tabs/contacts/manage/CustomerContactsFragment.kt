package com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.manage

import android.Manifest
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
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
import com.divesh.perfex.customers.domain.adapters.CustomerContactsAdapter
import com.divesh.perfex.customers.domain.interfaces.CustomerContactsInterface
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.add.AddContactActivity
import com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.edit.EditContactActivity
import com.divesh.perfex.databinding.FragmentCustomerContactsBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class CustomerContactsFragment : CoreFragment(), CustomerContactsInterface {
    private var _binding: FragmentCustomerContactsBinding? = null
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = CustomerContactsAdapter(arrayListOf(), this)
    private var userId = 0
    private var createContact = false
    private val viewModel: CustomerContactsViewModel by viewModels()
    private val binding get() = _binding!!
    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        // Inflate the layout for this fragment
        _binding = FragmentCustomerContactsBinding.inflate(inflater, container, false)
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
            viewModel.getCustomerContacts(userId)
            binding.swipeRefresh.isRefreshing = false
        }

        binding.addContacts.setOnClickListener {
            val intent = Intent(binding.root.context, AddContactActivity::class.java).apply {
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
        createContact = arguments?.getBoolean("createContact", false)!!
        if (userId > 0) {
            viewModel.getCustomerContacts(userId)
        } else {
            Toast.makeText(context, "Customer not found!", Toast.LENGTH_SHORT).show()
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

        viewModel.contactDeletedAtPosition.observe(viewLifecycleOwner) {
            if (it != null) {
                listAdapter.notifyItemRemoved(it)
                viewModel.getCustomerContacts(userId)
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun deleteContact(id: Int, position: Int) {
        Extensions().simpleAlert(
            getString(R.string.confirm_deletion),
            requireContext()
        ) { isConfirmed ->
            if (isConfirmed) {
                viewModel.deleteCustomerContact(id, position)
            }
        }
    }

    override fun editContact(record: CustomerResponseModel.Data.Contact) {
        val intent = Intent(binding.root.context, EditContactActivity::class.java).apply {
            putExtra("contact", record)
        }
        startActivity(intent)
    }

    override fun activeStatusChanged(isChecked: Boolean, contactId: Int) {
        viewModel.updateContactActiveStatus(isChecked, contactId)
    }

    override fun sendMail(email: String?) {
        if (!email.isNullOrBlank()) {
            context?.let { Extensions().sendMail(it, email) }
        }else{
            Toast.makeText(context, "Email not found!", Toast.LENGTH_SHORT).show()
        }
    }

    override fun dialCall(phoneNumber: String?) {
        if (!phoneNumber.isNullOrBlank()) {
            val pm: PackageManager = binding.root.context.packageManager
            val hasPerm = pm.checkPermission(
                Manifest.permission.CALL_PHONE,
                binding.root.context.packageName
            )
            if (hasPerm == PackageManager.PERMISSION_GRANTED) {
                try {
                    val intent = Intent(Intent.ACTION_CALL)
                    intent.data = Uri.parse("tel:$phoneNumber")
                    binding.root.context.startActivity(intent)
                } catch (e: Exception) {
                    Toast.makeText(context, "Invalid PhoneNumber", Toast.LENGTH_SHORT).show()
                }
            } else {
                Toast.makeText(binding.root.context, "Please allow permission!", Toast.LENGTH_SHORT)
                    .show()
            }
        } else {
            Toast.makeText(binding.root.context, "Invalid PhoneNumber", Toast.LENGTH_SHORT)
                .show()
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.getCustomerContacts(userId)
    }
}