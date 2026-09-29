package com.divesh.perfex.customers.presentation.manage

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
import com.divesh.perfex.customers.domain.adapters.CustomerListAdapter
import com.divesh.perfex.customers.domain.interfaces.CustomersListInterface
import com.divesh.perfex.customers.domain.models.CustomerListResponseModel
import com.divesh.perfex.customers.presentation.add.AddCustomerActivity
import com.divesh.perfex.customers.presentation.edit.EditCustomerActivity
import com.divesh.perfex.customers.presentation.view_customer.ViewCustomerActivity
import com.divesh.perfex.databinding.FragmentCustomersBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class CustomersFragment : CoreFragment(), CustomersListInterface {
    private var _binding: FragmentCustomersBinding? = null
    private val binding get() = _binding!!
    private val viewModel: CustomersViewModel by viewModels()
    private val userListAdapter = CustomerListAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentCustomersBinding.inflate(inflater, container, false)
        val view = binding.root
        askPermissions(arrayOf(Manifest.permission.CALL_PHONE))
        progressBar = binding.progressBar
        registerInternetConnectionReceiver()
        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = userListAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh()
            binding.swipeRefresh.isRefreshing = false
        }

        observeViewModel()

        binding.addCustomer.setOnClickListener {
            startActivity(Intent(context, AddCustomerActivity::class.java))
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

        return view
    }
    private fun observeViewModel() {
        viewModel.refresh()

        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                userListAdapter.updateList(it)
            }
        }

        viewModel.removedCustomerAtPosition.observe(viewLifecycleOwner){
            viewModel.refresh()
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
            if (!isError.equals("") && isError != null && isError != "null") {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(it, isError.toString(), Toast.LENGTH_SHORT).show()  }
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

    override fun deleteCustomer(customer: CustomerListResponseModel.Customer, position: Int) {
        if(hasInternet) {
            Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
                if (isConfirmed) {
                    viewModel.deleteCustomer(customer.userid, position)
                }
            }
        }
    }

    override fun editCustomer(customer: CustomerListResponseModel.Customer) {
        startActivity(Intent(context, EditCustomerActivity::class.java).apply {
            putExtra("userId", customer.userid)
        })
    }

    override fun viewCustomer(customer: CustomerListResponseModel.Customer) {
        startActivity(Intent(context, ViewCustomerActivity::class.java).apply {
            putExtra("userId", customer.userid)
        })
    }

    override fun dialCall(phoneNumber: String?) {
        if (!phoneNumber.isNullOrBlank()) {
            val pm: PackageManager =  binding.root.context.packageManager
            val hasPerm = pm.checkPermission(
                Manifest.permission.CALL_PHONE,
                binding.root.context.packageName
            )
            if (hasPerm == PackageManager.PERMISSION_GRANTED) {
                val intent = Intent(Intent.ACTION_CALL)
                intent.data = Uri.parse("tel:$phoneNumber")
                binding.root.context.startActivity(intent)
            }else{
                Toast.makeText(binding.root.context, "Please allow permission!", Toast.LENGTH_SHORT)
                    .show()
            }
        } else {
            Toast.makeText(binding.root.context, "Invalid PhoneNumber", Toast.LENGTH_SHORT)
                .show()
        }
    }

    override fun sendMail(email: String?) {
        if (!email.isNullOrBlank()) {
            context?.let { Extensions().sendMail(it, email) }
        }else{
            Toast.makeText(context, "Email not found!", Toast.LENGTH_SHORT).show()
        }
    }

    override fun activeStatusChanged(isChecked: Boolean, customerId: Int) {
        viewModel.updateActiveStatus(isChecked, customerId)
    }
}