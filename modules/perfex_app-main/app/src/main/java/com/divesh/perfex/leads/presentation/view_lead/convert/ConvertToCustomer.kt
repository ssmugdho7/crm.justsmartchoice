package com.divesh.perfex.leads.presentation.view_lead.convert

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.databinding.ActivityConvertToCustomerBinding
import com.google.android.material.button.MaterialButton
import com.divesh.perfex.core.helpers.Extensions
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ConvertToCustomer : AppCompatActivity() {
    private lateinit var binding: ActivityConvertToCustomerBinding
    private var leadId = 0
    private lateinit var progressBar: ProgressBar
    private val viewModel: ConvertToCustomerViewModel by viewModels()
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityConvertToCustomerBinding.inflate(layoutInflater)
        val view: View = binding.root
        setContentView(view)
        setSupportActionBar(binding.toolbar)
        observeViewModel()
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener{
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<MaterialButton>(R.id.toolbarConvert).setOnClickListener{
            if(validateForm()){
                var sendSetPasswordEmail = ""
                var doNotSendWelcomeEmail = ""
                if(binding.sendSetPasswordEmail.isChecked)
                    sendSetPasswordEmail = "on"
                if(binding.doNotSendWelcomeEmail.isChecked)
                    doNotSendWelcomeEmail = "on"

                viewModel.convertToCustomer(
                    leadId,
                    binding.firstName.editText?.text.toString(),
                    binding.lastName.editText?.text.toString(),
                    binding.position.editText?.text.toString(),
                    binding.email.editText?.text.toString(),
                    binding.company.editText?.text.toString(),
                    binding.phone.editText?.text.toString(),
                    binding.website.editText?.text.toString(),
                    binding.address.editText?.text.toString(),
                    binding.city.editText?.text.toString(),
                    binding.state.editText?.text.toString(),
                    binding.country.text.toString(),
                    binding.zipCode.editText?.text.toString(),
                    binding.password.editText?.text.toString(),
                    sendSetPasswordEmail,
                    doNotSendWelcomeEmail,
                    intent.getStringExtra("email").toString()
                )
                progressBar.visibility = View.VISIBLE
            }
        }
    }

    private fun observeViewModel() {
        viewModel.converted.observe(this){
            if(it){
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.loading.observe(this){
            if(it){
                progressBar.visibility = View.VISIBLE
            }else{
                progressBar.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(this){
            if(it != null){
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.countryListResponse.observe(this){
            val items = arrayListOf<String>()
            var selectedCountry : String? = null
            for(countries in it.countries){
                items.add(countries.long_name.toString())
                if(countries.country_id == viewModel.countryId.value){
                    selectedCountry = countries.long_name
                }
            }
            val adapter = ArrayAdapter(this@ConvertToCustomer, R.layout.list_item, items)
            binding.country.setAdapter(adapter)
            if(selectedCountry != null ){
                binding.country.setText(selectedCountry ,false)
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if(binding.firstName.editText?.text?.isEmpty() == true){
            isValid = false
            binding.firstName.error = getString(R.string.field_required)
        }
        if(binding.lastName.editText?.text?.isEmpty() == true){
            isValid = false
            binding.lastName.error = getString(R.string.field_required)
        }
        if(binding.company.editText?.text?.isEmpty() == true){
            isValid = false
            binding.company.error = getString(R.string.field_required)
        }
        if(binding.email.editText?.text?.isEmpty() == true){
            isValid = false
            binding.email.error = getString(R.string.field_required)
        }
        if(binding.password.editText?.text?.isEmpty() == true){
            isValid = false
            binding.password.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun setInitialData() {
        val name = intent.getStringExtra("name")
        val title = intent.getStringExtra("title")
        val email = intent.getStringExtra("email")
        val company = intent.getStringExtra("company")
        val phoneNumber = intent.getStringExtra("phonenumber")
        val website = intent.getStringExtra("website")
        val address = intent.getStringExtra("address")
        val city = intent.getStringExtra("city")
        val state = intent.getStringExtra("state")
        val country = intent.getIntExtra("country", 0)
        val zip = intent.getStringExtra("zip")
        leadId = intent.getIntExtra("id" , 0)

        //set title first
        if(title?.isNotEmpty() == true)
            binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.task_title , leadId.toString(), title)
        else
            binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.task_title , leadId.toString(), "")

        //set form data
        binding.address.editText?.setText(address)
        binding.city.editText?.setText(city)
        binding.company.editText?.setText(company)
        binding.email.editText?.setText(email)
        binding.phone.editText?.setText(phoneNumber)
        binding.state.editText?.setText(state)
        binding.position.editText?.setText(title)
        binding.website.editText?.setText(website)
        val nameArray = name?.split(" ")
        if(nameArray?.get(0)?.isNotBlank() == true)
            binding.firstName.editText?.setText(nameArray[0])

        try {
            if (nameArray?.get(1)?.isNotBlank() == true)
                binding.lastName.editText?.setText(nameArray[1])
        }catch (e: Exception){
            e.printStackTrace()
        }
        if(zip != null && zip != "0" && zip != "null") { binding.zipCode.editText?.setText("$zip") }
        viewModel.initCountryDropdown(country)
    }
}