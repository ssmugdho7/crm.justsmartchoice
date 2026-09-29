package com.divesh.perfex.support.presentation.add

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddTicketBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class AddTicketActivity : AppCompatActivity() {
    private lateinit var binding: ActivityAddTicketBinding
    private val viewModel: AddTicketViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddTicketBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        initViews()
        initClickListeners()
    }

    private fun initClickListeners() {
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener{
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarAddBtn).setOnClickListener{
            if(validateForm()){
                var contactId = 0
                var department = 0
                val projectId = null
                var assigned = 0
                var priority = 0
                var service = 0
                if(binding.assigned.text?.toString()?.isNotBlank() == true){
                    if(viewModel.initialTicketResponseModel.value?.staff?.isNotEmpty() == true){
                        viewModel.initialTicketResponseModel.value?.staff?.forEach {
                            if(binding.assigned.text?.toString() == "${it.firstname} ${it.lastname}"){
                                assigned = it.staffid
                                return@forEach
                            }
                        }
                    }
                }
                if(binding.contact.text?.toString()?.isNotBlank() == true){
                    if(viewModel.initialTicketResponseModel.value?.contacts?.isNotEmpty() == true){
                        viewModel.initialTicketResponseModel.value?.contacts?.forEach {
                            if(binding.contact.text?.toString() == "${it.firstname} ${it.lastname}"){
                                contactId = it.id
                                return@forEach
                            }
                        }
                    }
                }
                if(binding.department.text?.toString()?.isNotBlank() == true){
                    if(viewModel.initialTicketResponseModel.value?.departments?.isNotEmpty() == true){
                        viewModel.initialTicketResponseModel.value?.departments?.forEach {
                            if(binding.department.text?.toString() == it.name){
                                department = it.departmentid
                                return@forEach
                            }
                        }
                    }
                }
                if(binding.service.text?.toString()?.isNotBlank() == true){
                    if(viewModel.initialTicketResponseModel.value?.services?.isNotEmpty() == true){
                        viewModel.initialTicketResponseModel.value?.services?.forEach {
                            if(binding.service.text?.toString() == it.name){
                                service = it.serviceid
                                return@forEach
                            }
                        }
                    }
                }
                binding.priority.text.toString().let {
                    when (it) {
                        getString(R.string.low_priority) -> {
                            priority = 1
                        }
                        getString(R.string.medium_priority) -> {
                            priority = 2
                        }
                        getString(R.string.high_priority) -> {
                            priority = 3
                        }
                    }
                }
                viewModel.addTicket(binding.subject.editText?.text.toString(), contactId, binding.name.editText?.text.toString(), binding.email.editText?.text.toString(), department, binding.cc.editText?.text.toString(), null, assigned, priority, service, projectId, binding.description.editText?.text.toString())
            }
        }
        binding.ticketWithoutContact.setOnClickListener{
            if(binding.ticketWithoutContact.isChecked){
                binding.name.editText?.setText("")
                binding.email.editText?.setText("")
                binding.name.isFocusable = true
                binding.contact.visibility = View.GONE
            }else{
                binding.name.editText?.isFocusable = false
                binding.contact.visibility = View.VISIBLE
            }
        }

        binding.contact.setOnItemClickListener { _, _, position, _ ->
            viewModel.initialTicketResponseModel.value?.contacts.let {
                it?.forEachIndexed { index, contact ->
                   if(index == position){
                       binding.name.editText?.setText("${contact.firstname} ${contact.lastname}")
                       binding.email.editText?.setText(contact.email)
                       return@forEachIndexed
                   }
                }
            }
        }
    }

    private fun initViews() {
        progressBar = binding.progressBar
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.add_ticket)
    }

    private fun observeViewModel() {
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

        viewModel.leadAdded.observe(this){
            if(it){
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.initialTicketResponseModel.observe(this){
            if(it != null){
                val staffList = arrayListOf<String>()
                for(staff in it.staff){
                    staffList.add("${staff.firstname} ${staff.lastname}")
                }
                val adapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, staffList)
                binding.assigned.setAdapter(adapter)

                val contactList = arrayListOf<String>()
                for(contact in it.contacts){
                    contactList.add("${contact.firstname} ${contact.lastname}")
                }
                val contactListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, contactList)
                binding.contact.setAdapter(contactListAdapter)

                val departmentList = arrayListOf<String>()
                for(department in it.departments){
                    departmentList.add(department.name)
                }
                val departmentListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, departmentList)
                binding.department.setAdapter(departmentListAdapter)

                val knowledgeBaseList = arrayListOf<String>()
                if(it.knowledgeBase?.isNotEmpty() == true) {
                    for (knowledgeBase in it.knowledgeBase) {
                        knowledgeBaseList.add(knowledgeBase.name)
                    }
                }
                val knowledgeBaseListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, knowledgeBaseList)
                binding.knowledgeBaseLink.setAdapter(knowledgeBaseListAdapter)

                val preDefinedReplyList = arrayListOf<String>()
                for(preDefinedReply in it.preDefinedReplies){
                    knowledgeBaseList.add(preDefinedReply.name)
                }
                val preDefinedReplyListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, preDefinedReplyList)
                binding.predefinedReplies.setAdapter(preDefinedReplyListAdapter)

                val servicesList = arrayListOf<String>()
                if(it.services != null) {
                    for (service in it.services) {
                        servicesList.add(service.name)
                    }
                }
                val servicesListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, servicesList)
                binding.service.setAdapter(servicesListAdapter)

                val priorityList = arrayListOf<String>()
                priorityList.add(getString(R.string.low_priority))
                priorityList.add(getString(R.string.medium_priority))
                priorityList.add(getString(R.string.high_priority))
                val priorityListAdapter = ArrayAdapter(this@AddTicketActivity, R.layout.list_item, priorityList)
                binding.priority.setAdapter(priorityListAdapter)
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if(binding.assigned.text.isNullOrEmpty()){
            isValid = false
            binding.assigned.error = getString(R.string.field_required)
        }
        if(binding.subject.editText?.text.isNullOrEmpty()){
            isValid = false
            binding.subject.editText?.error = getString(R.string.field_required)
        }
        if(binding.ticketWithoutContact.isChecked){
            if(binding.name.editText?.text.isNullOrEmpty()){
                isValid = false
                binding.name.error = getString(R.string.field_required)
            }
            if(binding.email.editText?.text.isNullOrEmpty()){
                isValid = false
                binding.email.error = getString(R.string.field_required)
            }
        }else{
            if(binding.contact.text.isNullOrEmpty()){
                isValid = false
                binding.contact.error = getString(R.string.field_required)
            }
        }
        return isValid
    }
}