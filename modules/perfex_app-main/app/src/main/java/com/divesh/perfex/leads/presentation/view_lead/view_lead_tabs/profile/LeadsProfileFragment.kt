package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.profile

import android.graphics.Typeface
import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import androidx.appcompat.widget.AppCompatTextView
import androidx.core.content.ContextCompat
import androidx.fragment.app.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentLeadsProfileBinding
import com.divesh.perfex.leads.domain.models.ViewLeadResponseModel
import com.divesh.perfex.leads.presentation.manage.LeadsViewModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class LeadsProfileFragment : Fragment() {
    private var _binding: FragmentLeadsProfileBinding? = null
    private val binding get() = _binding!!
    private val viewModel: LeadsViewModel by viewModels()
    private val extensions = Extensions()
    private lateinit var leadId: String
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        // Inflate the layout for this fragment
        _binding = FragmentLeadsProfileBinding.inflate(inflater, container, false)
        setData()
        observeViewModel()
        return binding.root
    }

    private fun observeViewModel() {
        leadId = arguments?.getString("id").toString()
        viewModel.getLead(leadId)

        viewModel.lead.observe(viewLifecycleOwner) { lead ->
            if (lead != null) {
                if(lead.custom_fields_values?.isNotEmpty() == true){
                    showCustomFields(lead)
                }
            }
        }
    }

    private fun showCustomFields(lead: ViewLeadResponseModel.Lead) {
        binding.customFieldsRootView.visibility = View.VISIBLE
        val customFieldTextView = AppCompatTextView(binding.root.context).apply {
            setPadding(10, 10, 10, 10)
            setTypeface(null, Typeface.BOLD)
            setTextColor(ContextCompat.getColor(context, R.color.themeColorPrimary))
            setBackgroundColor(ContextCompat.getColor(context, R.color.CoralBlue))
            layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
            text = getString(R.string.custom_fields)
            textSize = 16F
        }
        binding.customFieldsRootView.addView(customFieldTextView)
        lead.custom_fields_values?.forEachIndexed { index, customFieldsValue ->
            val mainLinearLayout = LinearLayout(context).apply {
                setPadding(10, 10, 10, 10)
                orientation = LinearLayout.VERTICAL
                layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
            }
            val headingTextView = AppCompatTextView(binding.root.context).apply {
                setTextColor(ContextCompat.getColor(context, R.color.themeColorPrimary))
                layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
                text = getCustomFieldHeading(customFieldsValue)
                textSize = 16F
            }
            val valueTextView = AppCompatTextView(binding.root.context).apply {
                setTextColor(ContextCompat.getColor(context, R.color.black))
                layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
                text = customFieldsValue?.value
                textSize = 16F
            }
            val view = View(context).apply {
                layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, 2)
                setBackgroundColor(ContextCompat.getColor(context, R.color.lightGrey))
            }
            mainLinearLayout.addView(headingTextView)
            mainLinearLayout.addView(valueTextView)
            mainLinearLayout.addView(view)
            binding.customFieldsRootView.addView(mainLinearLayout)
        }
    }

    private fun getCustomFieldHeading(customFieldsValue: ViewLeadResponseModel.Lead.CustomFieldsValue?): CharSequence? {
        var value: String? = null
        if(customFieldsValue != null){
            viewModel.lead.value?.custom_fields?.forEach {
                if(it?.id == customFieldsValue.fieldid){
                    value = it?.name
                }
            }
        }
        return value
    }

    private fun setData() {
        extensions.setText(binding.address, arguments?.getString("address"))
        extensions.setText(binding.assigned, arguments?.getString("assigned"))
        extensions.setText(binding.city, arguments?.getString("city"))
        extensions.setText(binding.company, arguments?.getString("company"))
        extensions.setText(binding.description, arguments?.getString("description"))
        extensions.setText(binding.email, arguments?.getString("email"))
        extensions.setText(binding.leadValue, arguments?.getString("lead_value"))
        extensions.setText(binding.name, arguments?.getString("name"))
        extensions.setText(binding.phone, arguments?.getString("phonenumber"))
        extensions.setText(binding.source, arguments?.getString("source"))
        extensions.setText(binding.state, arguments?.getString("state"))
        extensions.setText(binding.status, arguments?.getString("status"))
        extensions.setText(binding.position, arguments?.getString("title"))
        extensions.setText(binding.website, arguments?.getString("website"))
        extensions.setText(binding.lastContact, arguments?.getString("lastcontact"))
        extensions.setText(binding.created, arguments?.getString("dateadded"))
        extensions.setText(binding.language, arguments?.getString("default_language"))
        extensions.setText(binding.country, arguments?.getString("countryName"))
        extensions.setText(binding.zipCode, arguments?.getString("zip"))

        if (arguments?.getString("is_public").toString() == "1")
            binding.isPublic.text = getString(R.string.yes)
        else
            binding.isPublic.text = getString(R.string.no)
    }
}