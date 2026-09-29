package com.divesh.perfex.leads.presentation.manage

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
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentManageLeadsBinding
import com.divesh.perfex.leads.domain.adapters.ManageLeadsAdapter
import com.divesh.perfex.leads.domain.interfaces.ManageLeadsInterface
import com.divesh.perfex.leads.domain.models.Lead
import com.divesh.perfex.leads.presentation.add_lead.AddLeadActivity
import com.divesh.perfex.leads.presentation.edit_lead.EditLeadActivity
import com.divesh.perfex.leads.presentation.view_lead.ViewLeadActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ManageLeadsFragment : Fragment(), ManageLeadsInterface {
    private var _binding: FragmentManageLeadsBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = ManageLeadsAdapter(arrayListOf(), this)
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: LeadsViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentManageLeadsBinding.inflate(inflater, container, false)
        val view = binding.root
        observeViewModel()
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
            viewModel.loadLeads()
            binding.swipeRefresh.isRefreshing = false
        }

        binding.addLeadsFAB.setOnClickListener {
            val intent = Intent(binding.root.context, AddLeadActivity::class.java)
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

    private fun observeViewModel() {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                val lottieAnimation = view?.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (it.isEmpty()) {
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                } else {
                    listAdapter.setRecords(it as List<Lead>)
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

        viewModel.internetProblem.observe(viewLifecycleOwner) { isError ->
            if (isError) {
                progressBar.visibility = View.GONE
                context?.let { Extensions().showMessage(binding.root, getString(R.string.no_internet))}
            }
        }

        viewModel.responseMessage.observe(viewLifecycleOwner) { isError ->
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

        viewModel.itemDeletedAtPosition.observe(viewLifecycleOwner) {
            if (it != null) {
                listAdapter.notifyItemRemoved(it)
                viewModel.loadLeads()
            }
        }
    }


    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onLeadDeleteClicked(leadId: Int, position: Int) {
        Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
            if (isConfirmed) {
                viewModel.deleteLead(leadId, position)
            }
        }
    }

    override fun onLeadEditClicked(lead: Lead) {
        val intent = Intent(context, EditLeadActivity::class.java)
        setIntent(intent, lead)
        startActivity(intent)
    }

    override fun onLeadViewClicked(lead: Lead) {
        val intent = Intent(context, ViewLeadActivity::class.java)
        setIntent(intent, lead)
        startActivity(intent)
    }

    override fun onDialCallClicked(phoneNumber: String?) {
        if (phoneNumber != "") {
            val pm: PackageManager = binding.root.context.packageManager
            val hasPerm = pm.checkPermission(
                Manifest.permission.CALL_PHONE, binding.root.context.packageName
            )
            if (hasPerm == PackageManager.PERMISSION_GRANTED) {
                try {
                    val intent = Intent(Intent.ACTION_CALL)
                    intent.data = Uri.parse("tel:$phoneNumber")
                    binding.root.context.startActivity(intent)
                }catch (e: Exception){
                    Toast.makeText(context, "Invalid PhoneNumber", Toast.LENGTH_SHORT).show()
                }
            } else {
                Toast.makeText(
                    binding.root.context,
                    "Please allow permission to dial call!",
                    Toast.LENGTH_SHORT
                ).show()
            }

        } else {
            Toast.makeText(binding.root.context, "Invalid PhoneNumber", Toast.LENGTH_SHORT)
                .show()
        }
    }

    override fun onSendMailClicked(email: String?) {
        if (email != null && email != "") {
            if(context != null) Extensions().sendMail(requireContext(), email)
        } else {
            Toast.makeText(context, "Email not found!", Toast.LENGTH_SHORT)
                .show()
        }
    }

    private fun setIntent(intent: Intent, lead: Lead) {
        intent.putExtra("address", lead.address)
        intent.putExtra("assigned", lead.assigned)
        intent.putExtra("city", lead.city)
        intent.putExtra("company", lead.company)
        intent.putExtra("countryName", lead.countryName)
        intent.putExtra("description", lead.description)
        intent.putExtra("email", lead.email)
        intent.putExtra("id", lead.id)
        intent.putExtra("lead_value", lead.lead_value)
        intent.putExtra("name", lead.name)
        intent.putExtra("phonenumber", lead.phonenumber)
        intent.putExtra("source", lead.source)
        intent.putExtra("state", lead.state)
        intent.putExtra("status", lead.status)
        intent.putExtra("title", lead.title)
        intent.putExtra("website", lead.website)
        intent.putExtra("zip", lead.zip)
        intent.putExtra("lastcontact", lead.lastcontact)
        intent.putExtra("date_converted", lead.date_converted)
        intent.putExtra("dateadded", lead.dateadded)
        intent.putExtra("is_public", lead.is_public)
        intent.putExtra("default_language", lead.default_language)
        intent.putExtra("custom_fields", lead)
    }

    override fun onResume() {
        super.onResume()
        viewModel.loadLeads()
    }
}