package com.divesh.perfex.customers.presentation.add

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.activities.CoreActivity
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.presentation.view_customer.ViewCustomerActivity
import com.divesh.perfex.databinding.ActivityAddCustomerBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint class AddCustomerActivity : CoreActivity() {
    private lateinit var binding: ActivityAddCustomerBinding
    private lateinit var progressBar: ProgressBar
    private val viewModel: AddCustomerViewModel by viewModels()
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
        binding = ActivityAddCustomerBinding.inflate(layoutInflater)
        registerInternetConnectionReceiver()
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)

        observeViewModel()
        setInitialData()
        initClickListeners()
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

        viewModel.createContact.observe(this) {
            if (it != null) {
                if(viewModel.customerAddedResponse.value?.status == 1){
                    startActivity(Intent(this, ViewCustomerActivity::class.java).apply {
                        putExtra("createContact", it)
                        putExtra("userId", viewModel.customerAddedResponse.value?.customer?.userid)
                    })
                    finish()
                }
            }
        }

        viewModel.groupsDropdowns.observe(this) {
            if (it != null) {
                for (groups in it) {
                    groupList  += groups.id
                    groups.name.let { groupName -> groupArray += groupName.toString() }
                }
                selectedGroups = BooleanArray(it.size)
                Extensions().transformIntoMultiSelectPicker(binding.groupsAtv, this, groupArray,  selectedGroups,"Select Groups") { selectedPositions ->
                    selectedGroupPositions = selectedPositions
                    val selectedGroupString = StringBuilder()
                    selectedGroups = BooleanArray(it.size)
                    selectedPositions.forEachIndexed {index, id ->
                        selectedGroupString.append(if((index+1) == selectedPositions.size){
                            groupArray[id]
                        }else{
                            "${groupArray[id]},"
                        })
                        selectedGroups?.set(index, true)
                    }
                    binding.groupsAtv.text = selectedGroupString.toString()
                }
            }
        }

        viewModel.countryDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedCountryName = ""
                for (countries in it) {
                    items.add(countries.short_name.toString())
                    countryIds.add(countries.country_id)
                    if(viewModel.defaultCountry.value == countries.country_id){
                        selectedCountryName = countries.short_name.toString()
                    }
                }
                val adapter = ArrayAdapter(this, R.layout.list_item, items)
                binding.country.setAdapter(adapter)
                binding.countryBilling.setAdapter(adapter)
                binding.countryShipping.setAdapter(adapter)
                if(selectedCountryName != "")
                    binding.country.setText(selectedCountryName, false)
            }
        }
    }
    private fun validateForm(): Boolean {
        var isValid = true
        if(!hasInternet){
            isValid = false
            Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
        }else if(binding.companyName.editText?.text.isNullOrEmpty()){
            isValid = false
            binding.companyName.editText?.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun initClickListeners(){
        binding.sameAsCustomerInfo.setOnCheckedChangeListener { _, isChecked ->
            if(isChecked){
                binding.addressBilling.editText?.text = binding.address.editText?.text
                binding.cityBilling.editText?.text = binding.city.editText?.text
                binding.stateBilling.editText?.text = binding.state.editText?.text
                binding.zipCodeBilling.editText?.text = binding.zipCode.editText?.text
                binding.countryBilling.text = binding.country.text
            }else{
                binding.addressBilling.editText?.text = null
                binding.cityBilling.editText?.text = null
                binding.stateBilling.editText?.text = null
                binding.zipCodeBilling.editText?.text = null
                binding.countryBilling.text = null
            }
        }
        binding.sameAsBillingAddress.setOnCheckedChangeListener { _, isChecked ->
            if(isChecked){
                binding.addressShipping.editText?.text = binding.addressBilling.editText?.text
                binding.cityShipping.editText?.text = binding.cityBilling.editText?.text
                binding.stateShipping.editText?.text = binding.stateBilling.editText?.text
                binding.zipCodeShipping.editText?.text = binding.zipCodeBilling.editText?.text
                binding.countryShipping.text = binding.countryBilling.text
            }else{
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
                if(sizeOfSelectedPositions > 0){
                    selectedGroupPositions.forEachIndexed { index, positionSelected ->
                        selectedGroupId += if(sizeOfSelectedPositions == (index+1)){
                            groupList[positionSelected].toString()
                        }else{
                            "${groupList[positionSelected]},"
                        }
                    }
                }
                viewModel.addCustomer(
                    false,
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
        binding.toolbar.findViewById<Button>(R.id.toolbarSaveAndCreateContact).setOnClickListener {
            if (validateForm()) {
                var selectedGroupId = ""
                val sizeOfSelectedPositions = selectedGroupPositions.size
                if(sizeOfSelectedPositions > 0){
                    selectedGroupPositions.forEachIndexed { index, positionSelected ->
                        selectedGroupId += if(sizeOfSelectedPositions == (index+1)){
                            groupList[positionSelected].toString()
                        }else{
                            "${groupList[positionSelected]},"
                        }
                    }
                }
                viewModel.addCustomer(
                    true,
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