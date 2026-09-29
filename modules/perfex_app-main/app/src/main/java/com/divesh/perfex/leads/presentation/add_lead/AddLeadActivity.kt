package com.divesh.perfex.leads.presentation.add_lead

import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.dynamic_form.DynamicFormHelper
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddLeadBinding
import com.divesh.perfex.leads.domain.models.CustomField
import com.divesh.perfex.leads.presentation.manage.LeadsViewModel
import dagger.hilt.android.AndroidEntryPoint
import java.util.*


@AndroidEntryPoint
class AddLeadActivity : AppCompatActivity() {
    private lateinit var binding: ActivityAddLeadBinding
    private val viewModel: LeadsViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private var dynamicFormHelper: DynamicFormHelper? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddLeadBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarUpdate).setOnClickListener {
            if (validateForm()) {
                viewModel.addLead(
                    binding.status.text.toString(),
                    binding.source.text.toString(),
                    binding.assigned.text.toString(),
                    binding.name.editText?.text.toString(),
                    binding.position.editText?.text.toString(),
                    binding.email.editText?.text.toString(),
                    binding.website.editText?.text.toString(),
                    binding.phone.editText?.text.toString(),
                    binding.company.editText?.text.toString(),
                    binding.address.editText?.text.toString(),
                    binding.city.editText?.text.toString(),
                    binding.state.editText?.text.toString(),
                    binding.country.text.toString(),
                    binding.zipCode.editText?.text.toString(),
                    binding.description.editText?.text.toString(),
                    binding.isPublic.isChecked,
                    binding.lastContact.text.toString(),
                    if (binding.contactedToday.isChecked) "on" else null,
                    binding.leadValue.editText?.text.toString(),
                    if(dynamicFormHelper != null) dynamicFormHelper!!.getFormData() else null
                )
            }
        }

        binding.contactedToday.setOnClickListener {
            if (binding.contactedToday.isChecked)
                binding.lastContact.visibility = View.GONE
            else
                binding.lastContact.visibility = View.VISIBLE
        }
    }

    private fun observeViewModel() {
        viewModel.loadInitialFormData()
        viewModel.loading.observe(this) {
            if (it) {
                progressBar.visibility = View.VISIBLE
            } else {
                progressBar.visibility = View.GONE
            }
        }

        viewModel.responseMessage.observe(this) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.internetProblem.observe(this) {
            if (it != null) {
                if (it) {
                    Extensions().showMessage(binding.root, getString(R.string.no_internet))
                }
            }
        }

        viewModel.itemAdded.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.statusDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedStatusName = ""
                for (leadStatus in it) {
                    leadStatus?.name?.let { it1 -> items.add(it1) }
                    if (leadStatus?.id == viewModel.defaultSelectedFields.value?.leads_default_status) {
                        selectedStatusName = leadStatus?.name.toString()
                    }
                }
                val adapter = ArrayAdapter(this@AddLeadActivity, R.layout.list_item, items)
                binding.status.setAdapter(adapter)
                if (selectedStatusName != "")
                    binding.status.setText(selectedStatusName, false)
            }
        }

        viewModel.sourceDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedSourceName = ""
                for (leadSource in it) {
                    leadSource?.name?.let { it1 -> items.add(it1) }
                    if (leadSource?.id == viewModel.defaultSelectedFields.value?.leads_default_source) {
                        selectedSourceName = leadSource?.name.toString()
                    }
                }
                val adapter = ArrayAdapter(this@AddLeadActivity, R.layout.list_item, items)
                binding.source.setAdapter(adapter)
                if (selectedSourceName != "")
                    binding.source.setText(selectedSourceName, false)
            }
        }

        viewModel.countryDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedCountryName = ""
                for (countries in it) {
                    items.add(countries.short_name.toString())
                    if (viewModel.defaultSelectedFields.value?.leads_default_country == countries.country_id) {
                        selectedCountryName = countries.short_name.toString()
                    }
                }
                val adapter = ArrayAdapter(this@AddLeadActivity, R.layout.list_item, items)
                binding.country.setAdapter(adapter)
                if (selectedCountryName != "")
                    binding.country.setText(selectedCountryName, false)
            }
        }

        viewModel.assigneeDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedStaff = ""
                for (staff in it) {
                    items.add("${staff?.firstname} ${staff?.lastname}")
                    if (staff?.staffid == viewModel.staffId) {
                        selectedStaff = "${staff.firstname} ${staff.lastname}"
                    }
                }
                val adapter = ArrayAdapter(this@AddLeadActivity, R.layout.list_item, items)
                binding.assigned.setAdapter(adapter)
                if (selectedStaff != "")
                    binding.assigned.setText(selectedStaff, false)
            }
        }

        viewModel.customFields.observe(this) {
            if (it != null)
                setCustomFields(it)
        }
    }

    private fun setCustomFields(customFields: List<CustomField?>) {
        binding.customFieldsRootView.visibility = View.VISIBLE
        dynamicFormHelper = DynamicFormHelper(
            this,
            customFields,
            binding.customFieldsParentView,
            "leads"
        )
        dynamicFormHelper!!.buildAndApplyForm()
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if (binding.status.text.isNullOrEmpty()) {
            isValid = false
            binding.status.error = getString(R.string.field_required)
        }
        if (binding.source.text.isNullOrEmpty()) {
            isValid = false
            binding.source.error = getString(R.string.field_required)
        }
        if (binding.name.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.name.error = getString(R.string.field_required)
        }
        if (!binding.contactedToday.isChecked) {
            if (binding.lastContact.text.toString() == "") {
                isValid = false
                binding.contactedToday.error = getString(R.string.field_required)
            }
        }
        if(dynamicFormHelper != null) {
            isValid = dynamicFormHelper!!.validateForm()
        }
        return isValid
    }

    private fun setInitialData() {
        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text =
            getString(R.string.add_lead)
        Extensions().transformIntoDatePicker(binding.lastContact, this, null, Date())
    }
}