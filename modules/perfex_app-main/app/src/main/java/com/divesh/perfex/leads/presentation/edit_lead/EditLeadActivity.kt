package com.divesh.perfex.leads.presentation.edit_lead

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.core.helpers.dynamic_form.DynamicFormHelper
import com.divesh.perfex.databinding.ActivityEditLeadBinding
import com.divesh.perfex.leads.domain.models.CustomField
import com.divesh.perfex.leads.presentation.manage.LeadsViewModel
import dagger.hilt.android.AndroidEntryPoint
import java.util.*

@AndroidEntryPoint
class EditLeadActivity : AppCompatActivity() {
    private lateinit var binding: ActivityEditLeadBinding
    private lateinit var progressBar: ProgressBar
    private val viewModel: LeadsViewModel by viewModels()
    private var country = -1
    private var source: String? = null
    private var status: String? = null
    private var assigned: String? = null
    private var leadId = 0
    private var dynamicFormHelper: DynamicFormHelper? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEditLeadBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        setInitialData()
        observeViewModel()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.toolbar.findViewById<Button>(R.id.toolbarUpdate).setOnClickListener {
            if (validateForm()) {
                viewModel.updateLead(
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
                    binding.leadValue.editText?.text.toString(),
                    leadId.toString(),
                    if (dynamicFormHelper != null) dynamicFormHelper!!.getFormData() else null
                )
            }
        }
    }

    private fun observeViewModel() {
        viewModel.getLead(leadId.toString())
        viewModel.lead.observe(this) {
            if (it != null) {
                viewModel.loadInitialFormData()
            }
        }
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
                for (leadStatus in it) {
                    leadStatus?.name?.let { it1 -> items.add(it1) }
                }
                val adapter = ArrayAdapter(this, R.layout.list_item, items)
                binding.status.setAdapter(adapter)
                if (status != null) {
                    binding.status.setText(status, false)
                }
            }
        }

        viewModel.sourceDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                for (leadSource in it) {
                    leadSource?.name?.let { it1 -> items.add(it1) }
                }
                val adapter = ArrayAdapter(this, R.layout.list_item, items)
                binding.source.setAdapter(adapter)
                if (source != null) {
                    binding.source.setText(source, false)
                }
            }
        }

        viewModel.countryDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                var selectedCountry: String? = null
                for (countries in it) {
                    items.add(countries.long_name.toString())
                    if (countries.country_id == country) {
                        selectedCountry = countries.long_name
                    }
                }
                val adapter = ArrayAdapter(this, R.layout.list_item, items)
                binding.country.setAdapter(adapter)
                if (selectedCountry != null) {
                    binding.country.setText(selectedCountry, false)
                }
            }
        }

        viewModel.assigneeDropdowns.observe(this) {
            if (it != null) {
                val items = arrayListOf<String>()
                for (staff in it) {
                    items.add("${staff?.firstname} ${staff?.lastname}")
                }
                val adapter = ArrayAdapter(this, R.layout.list_item, items)
                binding.assigned.setAdapter(adapter)
            }
        }

        viewModel.customFields.observe(this) {
            if (it != null)
                setCustomFields(it)
        }

        viewModel.internetProblem.observe(this) {
            if (it != null) {
                Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun setCustomFields(customFields: List<CustomField?>) {
        binding.customFieldsRootView.visibility = View.VISIBLE
        dynamicFormHelper =
            DynamicFormHelper(this, customFields, binding.customFieldsParentView, "leads")
        dynamicFormHelper!!.setCustomFieldValues(viewModel.lead.value?.custom_fields_values)
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
        return isValid
    }

    private fun setInitialData() {
        //set title first
        val address = intent.getStringExtra("address")
        val city = intent.getStringExtra("city")
        val company = intent.getStringExtra("company")
        assigned = intent.getStringExtra("assigned")
        country = intent.getIntExtra("country", -1)
        source = intent.getStringExtra("source")
        status = intent.getStringExtra("status")
        val description = intent.getStringExtra("description")
        val email = intent.getStringExtra("email")
        leadId = intent.getIntExtra("id", 0)
        val leadValue = intent.getStringExtra("lead_value")
        val name = intent.getStringExtra("name")
        val phonenumber = intent.getStringExtra("phonenumber")
        val state = intent.getStringExtra("state")
        val title = intent.getStringExtra("title")
        val website = intent.getStringExtra("website")
        val lastcontact = intent.getStringExtra("lastcontact")
        val zip = intent.getIntExtra("zip", 0)
        val isPublic = intent.getIntExtra("is_public", 0)

        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text =
            getString(R.string.task_title, leadId.toString(), name)

        //set form data
        binding.address.editText?.setText(address)
        binding.city.editText?.setText(city)
        binding.company.editText?.setText(company)
        binding.description.editText?.setText(description)
        binding.email.editText?.setText(email)
        binding.leadValue.editText?.setText(leadValue.toString())
        binding.name.editText?.setText(name)
        binding.phone.editText?.setText(phonenumber)
        binding.state.editText?.setText(state)
        binding.position.editText?.setText(title)
        binding.website.editText?.setText(website)
        if (lastcontact != null) binding.lastContact.text = lastcontact
        if (zip > 0) {
            binding.zipCode.editText?.setText("$zip")
        }
        if (isPublic == 1) {
            binding.isPublic.isChecked = true
        }

        Extensions().transformIntoDatePicker(binding.lastContact, this, null, Date())
    }
}