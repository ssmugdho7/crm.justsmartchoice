package com.divesh.perfex.customers.presentation.edit

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import androidx.core.text.parseAsHtml
import com.divesh.perfex.R
import com.divesh.perfex.core.activities.CoreActivity
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.presentation.view_customer.ViewCustomerActivity
import com.divesh.perfex.databinding.ActivityEditCustomerBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class EditCustomerActivity : CoreActivity() {
    private lateinit var binding: ActivityEditCustomerBinding
    private lateinit var progressBar: ProgressBar
    private val viewModel: EditCustomerViewModel by viewModels()
    private var selectedGroupPositions: ArrayList<Int> = ArrayList()
    private var selectedGroups: BooleanArray? = null
    private var groupList: ArrayList<Int> = ArrayList()
    private var groupArray: Array<String> = arrayOf()

    private var selectedCountry = 0
    private var countryIds = arrayListOf<Int>()
    private var selectedBillingCountry = 0
    private var selectedShippingCountry = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEditCustomerBinding.inflate(layoutInflater)
        registerInternetConnectionReceiver()
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        setInitialData()
        initClickListeners()
        checkIntent()
    }

    private fun checkIntent() {
        val customerId = intent.getIntExtra("userId", 0)
        viewModel.getCustomer(customerId)
    }

    private fun observeViewModel() {
        viewModel.loading.observe(this) {
            if (it) {
                progressBar.visibility = View.VISIBLE
            } else {
                progressBar.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(this) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.customerUpdated.observe(this) {
            if (it != null) {
                if (it) {
                    startActivity(Intent(this, ViewCustomerActivity::class.java).apply {
                        putExtra("createContact", false)
                        putExtra("userId", viewModel.customer.value?.data?.client?.userid)
                    })
                    finish()
                }
            }
        }

        viewModel.customer.observe(this) {
            if (it != null) {
                val client = it.data.client
                binding.title.text =
                    getString(R.string.task_title, client.userid.toString(), client.company)
                binding.companyName.editText?.setText(client.company)
                if (client.vat.toString().isNotBlank() && client.vat.toString() != "null")
                    binding.vatNumber.editText?.setText(client.vat.toString())
                if (client.phonenumber.toString().isNotBlank()  && client.phonenumber.toString() != "null")
                    binding.phoneNumber.editText?.setText(client.phonenumber.toString())
                binding.website.editText?.setText(client.website)

                if (client.address != null)
                    binding.address.editText?.setText(client.address.parseAsHtml())
                binding.city.editText?.setText(client.city)
                binding.state.editText?.setText(client.state)
                if (client.zip.toString().isNotBlank()  && client.zip.toString() != "null")
                    binding.zipCode.editText?.setText(client.zip.toString())

                if (client.billing_street != null)
                    binding.addressBilling.editText?.setText(client.billing_street.parseAsHtml())
                binding.cityBilling.editText?.setText(client.billing_city)
                binding.stateBilling.editText?.setText(client.billing_state)
                if (client.billing_zip.toString().isNotBlank() && client.billing_zip.toString() != "null")
                binding.zipCodeBilling.editText?.setText(client.billing_zip.toString())

                if (client.shipping_street != null)
                    binding.addressShipping.editText?.setText(client.shipping_street.parseAsHtml())
                binding.cityShipping.editText?.setText(client.shipping_city)
                binding.stateShipping.editText?.setText(client.shipping_state)
                if (client.shipping_zip.toString().isNotBlank()  && client.shipping_zip.toString() != "null")
                    binding.zipCodeShipping.editText?.setText(client.shipping_zip.toString())

                if (it.data.countries != null) {
                    val items = arrayListOf<String>()
                    for (countries in it.data.countries) {
                        items.add(countries.short_name.toString())
                        countryIds.add(countries.country_id)
                    }
                    val adapter = ArrayAdapter(this, R.layout.list_item, items)
                    binding.country.setAdapter(adapter)
                    binding.countryBilling.setAdapter(adapter)
                    binding.countryShipping.setAdapter(adapter)

                    var selectedCountryCount = 0
                    it.data.countries.forEach { country ->
                        if (country.country_id == client.country?.toInt()) {
                            binding.country.setText(country.short_name, false)
                            selectedCountry = country.country_id
                            selectedCountryCount++
                        }
                        if (country.country_id == client.billing_country?.toInt()) {
                            binding.countryBilling.setText(country.short_name, false)
                            selectedBillingCountry = country.country_id
                            selectedCountryCount++
                        }
                        if (country.country_id == client.shipping_country?.toInt()) {
                            binding.countryShipping.setText(country.short_name, false)
                            selectedShippingCountry = country.country_id
                            selectedCountryCount++
                        }

                        if (selectedCountryCount == 3) {
                            return@forEach
                        }
                    }
                }

                if (it.data.groups != null) {
                    for (groups in it.data.groups) {
                        groupList += groups.id
                        groups.name.let { groupName -> groupArray += groupName.toString() }
                    }
                    selectedGroups = BooleanArray(it.data.groups.size)
                    Extensions().transformIntoMultiSelectPicker(
                        binding.groupsAtv,
                        this,
                        groupArray,
                        selectedGroups,
                        "Select Groups"
                    ) { selectedPositions ->
                        selectedGroupPositions = selectedPositions
                        val selectedGroupString = StringBuilder()
                        selectedGroups = BooleanArray(it.data.groups.size)
                        selectedPositions.forEachIndexed { index, id ->
                            selectedGroupString.append(
                                if ((index + 1) == selectedPositions.size) {
                                    groupArray[id]
                                } else {
                                    "${groupArray[id]},"
                                }
                            )
                            selectedGroups?.set(index, true)
                        }
                        binding.groupsAtv.text = selectedGroupString.toString()
                    }
                    it.data.customer_groups?.forEach { customerGroup ->
                        if (customerGroup.customer_id == client.userid.toString() && customerGroup.groupid != null) {
                            selectedGroups?.set(
                                groupList.indexOf(customerGroup.groupid.toInt()),
                                true
                            )
                        }
                        Extensions().transformIntoMultiSelectPicker(
                            binding.groupsAtv,
                            this,
                            groupArray,
                            selectedGroups,
                            "Select Groups"
                        ) { selectedPositions ->
                            selectedGroupPositions = selectedPositions
                            val selectedGroupString = StringBuilder()
                            selectedGroups = BooleanArray(it.data.groups.size)
                            selectedPositions.forEachIndexed { index, id ->
                                selectedGroupString.append(
                                    if ((index + 1) == selectedPositions.size) {
                                        groupArray[id]
                                    } else {
                                        "${groupArray[id]},"
                                    }
                                )
                                selectedGroups?.set(index, true)
                            }
                            binding.groupsAtv.text = selectedGroupString.toString()
                        }
                    }
                }
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if (!hasInternet) {
            isValid = false
            Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
        } else if (binding.companyName.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.companyName.editText?.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun initClickListeners() {
        binding.sameAsCustomerInfo.setOnCheckedChangeListener { _, isChecked ->
            if (isChecked) {
                binding.addressBilling.editText?.text = binding.address.editText?.text
                binding.cityBilling.editText?.text = binding.city.editText?.text
                binding.stateBilling.editText?.text = binding.state.editText?.text
                binding.zipCodeBilling.editText?.text = binding.zipCode.editText?.text
                binding.countryBilling.text = binding.country.text
                selectedBillingCountry = selectedCountry
            } else {
                binding.addressBilling.editText?.text = null
                binding.cityBilling.editText?.text = null
                binding.stateBilling.editText?.text = null
                binding.zipCodeBilling.editText?.text = null
                binding.countryBilling.text = null
            }
        }
        binding.sameAsBillingAddress.setOnCheckedChangeListener { _, isChecked ->
            if (isChecked) {
                binding.addressShipping.editText?.text = binding.addressBilling.editText?.text
                binding.cityShipping.editText?.text = binding.cityBilling.editText?.text
                binding.stateShipping.editText?.text = binding.stateBilling.editText?.text
                binding.zipCodeShipping.editText?.text = binding.zipCodeBilling.editText?.text
                binding.countryShipping.text = binding.countryBilling.text
                selectedShippingCountry = selectedBillingCountry
            } else {
                binding.addressShipping.editText?.text = null
                binding.cityShipping.editText?.text = null
                binding.stateShipping.editText?.text = null
                binding.zipCodeShipping.editText?.text = null
                binding.countryShipping.text = null
            }
        }
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarSave).setOnClickListener {
            if (validateForm()) {
                var selectedGroupId = ""
                val sizeOfSelectedPositions = selectedGroupPositions.size
                if (sizeOfSelectedPositions > 0) {
                    selectedGroupPositions.forEachIndexed { index, positionSelected ->
                        selectedGroupId += if (sizeOfSelectedPositions == (index + 1)) {
                            groupList[positionSelected].toString()
                        } else {
                            "${groupList[positionSelected]},"
                        }
                    }
                }
                viewModel.updateCustomer(
                    binding.companyName.editText?.text.toString(),
                    binding.vatNumber.editText?.text.toString(),
                    binding.phoneNumber.editText?.text.toString(),
                    binding.website.editText?.text.toString(),
                    selectedGroupId,
                    binding.address.editText?.text.toString(),
                    binding.city.editText?.text.toString(),
                    binding.state.editText?.text.toString(),
                    binding.zipCode.editText?.text.toString(),
                    selectedCountry,

                    binding.addressBilling.editText?.text.toString(),
                    binding.cityBilling.editText?.text.toString(),
                    binding.stateBilling.editText?.text.toString(),
                    binding.zipCodeBilling.editText?.text.toString(),
                    selectedBillingCountry,

                    binding.addressShipping.editText?.text.toString(),
                    binding.cityShipping.editText?.text.toString(),
                    binding.stateShipping.editText?.text.toString(),
                    binding.zipCodeShipping.editText?.text.toString(),
                    selectedShippingCountry
                )
            }
        }
    }

    private fun setInitialData() {
        progressBar = binding.progressBar
        binding.country.setOnItemClickListener { _, _, position, _ ->
            selectedCountry = countryIds[position]
        }
        binding.countryShipping.setOnItemClickListener { _, _, position, _ ->
            selectedShippingCountry = countryIds[position]
        }
        binding.countryBilling.setOnItemClickListener { _, _, position, _ ->
            selectedBillingCountry = countryIds[position]
        }
    }
}