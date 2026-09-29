package com.divesh.perfex.customers.presentation.view_customer.tabs.profile

import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import android.widget.Toast
import androidx.core.text.parseAsHtml
import androidx.fragment.app.viewModels
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import com.divesh.perfex.databinding.FragmentViewCustomerProfileBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ViewCustomerProfileFragment : Fragment() {
    private var _binding: FragmentViewCustomerProfileBinding? = null
    private lateinit var progressBar: ProgressBar
    private val viewModel: ViewCustomerProfileViewModel by viewModels()
    private val binding get() = _binding!!
    private var countryId = 0
    private var billingCountryId = 0
    private var shippingCountryId = 0
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        // Inflate the layout for this fragment
        _binding = FragmentViewCustomerProfileBinding.inflate(inflater, container, false)
        val view = binding.root
        progressBar = binding.progressBar
        checkArguments()
        observeViewModel()
        return view
    }

    private fun checkArguments() {
        val userId = arguments?.getInt("userId", 0)
        if (userId != null) {
            if (userId > 0) {
                viewModel.getCustomer(userId)
            } else {
                Toast.makeText(context, "Customer not found!", Toast.LENGTH_SHORT).show()
            }
        } else {
            Toast.makeText(context, "Customer not found!", Toast.LENGTH_SHORT).show()
        }
    }

    private fun observeViewModel() {
        viewModel.loading.observe(viewLifecycleOwner) {
            if (it) {
                progressBar.visibility = View.VISIBLE
            } else {
                progressBar.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.customer.observe(viewLifecycleOwner) {
            if (it != null) {
                val client = it.data.client

                binding.company.text = client.company
                Extensions().setText(binding.vat, client.vat)
                binding.phone.text = client.phonenumber.toString()
                binding.website.text = client.website

                if (client.address != null)
                    binding.address.text = client.address.parseAsHtml()
                binding.city.text = client.city
                binding.state.text = client.state
                if (client.zip.toString() != "null" && client.zip.toString().isNotBlank())
                    binding.zipCode.text = client.zip.toString()

                if (client.billing_street != null)
                    binding.addressBilling.text = client.billing_street.parseAsHtml()
                binding.cityBilling.text = client.billing_city
                binding.stateBilling.text = client.billing_state
                if (client.billing_zip.toString() != "null" && client.billing_zip.toString()
                        .isNotBlank()
                )
                    binding.zipCodeBilling.text = client.billing_zip.toString()

                if (client.shipping_street != null)
                    binding.addressShipping.text = client.shipping_street.parseAsHtml()
                binding.cityShipping.text = client.shipping_city
                binding.stateShipping.text = client.shipping_state
                if (client.shipping_zip.toString() != "null" && client.shipping_zip.toString()
                        .isNotBlank()
                )
                binding.zipCodeShipping.text = client.shipping_zip.toString()

                if (it.data.customer_groups != null) {
                    var groups = ""
                    it.data.customer_groups.forEach { group ->
                        val tempGroup = getGroupNameFromId(group.groupid, it.data.groups)
                        if (tempGroup != null) {
                            groups += "${tempGroup},"
                        }
                    }
                    binding.groups.text = groups
                }

                if (client.country != null && client.country.isNotBlank()) {
                    countryId = client.country.toInt()
                    if (client.billing_country != null)
                        billingCountryId = client.billing_country.toInt()

                    if (client.shipping_country != null)
                        shippingCountryId = client.shipping_country.toInt()
                }
                viewModel.initCountryDropdown()
            }
        }

        viewModel.countryListResponse.observe(viewLifecycleOwner) {
            it?.countries?.forEach { country ->
                if (country.country_id == countryId) {
                    binding.country.text = country.short_name
                }
                if (country.country_id == shippingCountryId) {
                    binding.countryShipping.text = country.short_name
                }
                if (country.country_id == billingCountryId) {
                    binding.countryBilling.text = country.short_name
                }
            }
        }
    }

    private fun getGroupNameFromId(
        groupid: String?,
        groups: List<CustomerResponseModel.Data.Group>?
    ): String? {
        if (groupid != null && groups != null) {
            groups.forEach {
                if (it.id == groupid.toInt()) {
                    return it.name
                }
            }
        }
        return null
    }
}