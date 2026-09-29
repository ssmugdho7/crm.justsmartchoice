package com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.add

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddContactBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class AddContactActivity : AppCompatActivity() {
    private lateinit var binding: ActivityAddContactBinding
    private val viewModel: AddContactViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private var userId: Int? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddContactBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.toolbar.findViewById<Button>(R.id.addContactBtn).setOnClickListener {
            if (validateForm()) {
                val permissionsArray: ArrayList<Int> = arrayListOf()
                if (binding.invoicesPermissionSwitch.isChecked) {
                    permissionsArray.add(1)
                }
                if (binding.estimatesPermissionSwitch.isChecked) {
                    permissionsArray.add(2)
                }
                if (binding.contractsPermissionSwitch.isChecked) {
                    permissionsArray.add(3)
                }
                if (binding.proposalsPermissionSwitch.isChecked) {
                    permissionsArray.add(4)
                }
                if (binding.supportPermissionSwitch.isChecked) {
                    permissionsArray.add(5)
                }
                if (binding.proposalsPermissionSwitch.isChecked) {
                    permissionsArray.add(6)
                }
                viewModel.addContact(
                    userId,
                    binding.firstName.editText?.text.toString(),
                    binding.lastName.editText?.text.toString(),
                    binding.position.editText?.text.toString(),
                    binding.email.editText?.text.toString(),
                    binding.phone.editText?.text.toString(),
                    "",
                    "",
                    "",
                    binding.password.editText?.text.toString(),
                    if (binding.primaryContact.isChecked) "on" else null,
                    if (binding.doNotSetWelcomeEmail.isChecked) "on" else null,
                    if (binding.sendSetPasswordEmail.isChecked) "on" else null,
                    permissionsArray,
                    if (binding.invoiceNotificationSwitch.isChecked) "invoice_emails" else null,
                    if (binding.estimatesNotificationSwitch.isChecked) "estimate_emails" else null,
                    if (binding.creditNoteSwitch.isChecked) "credit_note_emails" else null,
                    if (binding.projectNotificationSwitch.isChecked) "project_emails" else null,
                    if (binding.ticketsNotificationSwitch.isChecked) "ticket_emails" else null,
                    if (binding.tasksNotificationSwitch.isChecked) "task_emails" else null,
                    if (binding.contractNotificationSwitch.isChecked) "contract_emails" else null,
                )
            }
        }
    }

    private fun observeViewModel() {
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

        viewModel.itemAdded.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if (binding.firstName.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.firstName.error = getString(R.string.field_required)
        }
        if (binding.lastName.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.lastName.error = getString(R.string.field_required)
        }
        if (binding.email.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.email.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun setInitialData() {
        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text =
            getString(R.string.add_contact)
        userId = intent.getIntExtra("userId", 0)
        if (userId == 0) {
            Toast.makeText(this, "User not found!", Toast.LENGTH_SHORT).show()
            onBackPressedDispatcher.onBackPressed()
        }
    }
}