package com.divesh.perfex.customers.presentation.view_customer.tabs.notes.add

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddCustomerNoteBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class AddCustomerNoteActivity : AppCompatActivity() {
    private lateinit var binding: ActivityAddCustomerNoteBinding
    private val viewModel: AddNoteViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private var userId: Int? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddCustomerNoteBinding.inflate(layoutInflater)
        userId = intent.getIntExtra("userId", 0)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.toolbar.findViewById<Button>(R.id.addBtn).setOnClickListener {
            if (validateForm()) {
                viewModel.addNote(
                    userId!!,
                    "customer",
                    binding.description.editText?.text.toString()
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

        viewModel.loadError.observe(this) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.noteAdded.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if (binding.description.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.description.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun setInitialData() {
        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.add_notes)
    }
}