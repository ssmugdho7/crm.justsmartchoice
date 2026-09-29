package com.divesh.perfex.sales.presentation.invoices.presentation.utils

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.divesh.perfex.R
import com.divesh.perfex.databinding.AddressPickerBottomSheetBinding
import com.divesh.perfex.sales.domain.interfaces.AddressUpdatedInterface
import com.divesh.perfex.sales.domain.models.AddressPickerData
import com.divesh.perfex.sales.domain.models.Country

class AddressPickerBottomSheet(
    private val listener: AddressUpdatedInterface,
    private val countries: List<Country?>?,
    private var addressPickerData: AddressPickerData? = null,
) :
    BottomSheetDialogFragment() {
    private lateinit var _binding: AddressPickerBottomSheetBinding
    private val binding get() = _binding
    private var countryNames = arrayListOf<String>()
    private var billingCountry: Country? = null
    private var shippingCountry: Country? = null
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = AddressPickerBottomSheetBinding.inflate(inflater)
        val view = binding.root
        initViews()
        initObservers()
        return view
    }

    private fun initObservers() {
        binding.allowShipping.setOnCheckedChangeListener { _, isChecked ->
            if (isChecked) {
                binding.shippingContainer.visibility = View.VISIBLE
            } else {
                binding.shippingContainer.visibility = View.GONE
            }
        }
    }

    private fun initViews() {
        if (countries != null) {
            if (countries.isNotEmpty()) {
                countries.forEach { country ->
                    country?.short_name?.let { it1 -> countryNames.add(it1) }
                }
                val countryAdapter =
                    context?.let { ArrayAdapter(it, R.layout.list_item, countryNames) }
                binding.countryBilling.setAdapter(countryAdapter)
                binding.countryShipping.setAdapter(countryAdapter)
            }
        }
        setInitialData()

        binding.close.setOnClickListener {
            dismiss()
        }

        binding.countryBilling.setOnItemClickListener { _, _, position, _ ->
            billingCountry = countries?.get(position)
        }
        binding.countryShipping.setOnItemClickListener { _, _, position, _ ->
            shippingCountry = countries?.get(position)
        }

        binding.save.setOnClickListener {
            listener.onAddressUpdated(
                AddressPickerData(
                    binding.streetBilling.editText?.text.toString(),
                    binding.cityBilling.editText?.text.toString(),
                    binding.stateBilling.editText?.text.toString(),
                    binding.zipBilling.editText?.text.toString(),
                    billingCountry,
                    binding.allowShipping.isChecked,
                    binding.showShippingDetailsOnInvoice.isChecked,
                    binding.streetShipping.editText?.text.toString(),
                    binding.cityShipping.editText?.text.toString(),
                    binding.stateShipping.editText?.text.toString(),
                    binding.zipShipping.editText?.text.toString(),
                    shippingCountry
            ))
            dismiss()
        }
    }

    private fun setInitialData() {
        if (addressPickerData != null) {
            binding.streetBilling.editText?.setText(addressPickerData?.streetBilling)
            binding.streetShipping.editText?.setText(addressPickerData?.streetShipping)
            binding.cityBilling.editText?.setText(addressPickerData?.cityBilling)
            binding.cityShipping.editText?.setText(addressPickerData?.cityShipping)
            binding.stateBilling.editText?.setText(addressPickerData?.stateBilling)
            binding.stateShipping.editText?.setText(addressPickerData?.stateShipping)
            binding.zipBilling.editText?.setText(addressPickerData?.zipBilling)
            binding.zipShipping.editText?.setText(addressPickerData?.zipShipping)
            binding.countryBilling.setText(addressPickerData?.countryBilling?.short_name, false)
            binding.countryShipping.setText(addressPickerData?.countryShipping?.short_name, false)
            binding.showShippingDetailsOnInvoice.isChecked =
                addressPickerData?.showShippingDetailsOnInvoice == true
            if (addressPickerData?.allowShipping == true) {
                binding.allowShipping.isChecked = true
                binding.shippingContainer.visibility = View.VISIBLE
            } else {
                binding.allowShipping.isChecked = false
                binding.shippingContainer.visibility = View.GONE
            }
        } else {
            addressPickerData = AddressPickerData(
                null,
                null,
                null,
                null,
                null,
                allowShipping = false,
                showShippingDetailsOnInvoice = false,
                streetShipping = null,
                cityShipping = null,
                stateShipping = null,
                zipShipping = null,
                countryShipping = null,
            )
        }
    }
}