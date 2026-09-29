package com.divesh.perfex.customers.presentation.view_customer.tabs.notes.edit

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.domain.models.CustomerNotesResponseModel
import com.divesh.perfex.databinding.ActivityEditNoteBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class EditNoteActivity : AppCompatActivity() {
    private lateinit var binding: ActivityEditNoteBinding
    private val viewModel: EditNoteViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private lateinit var note: CustomerNotesResponseModel.Note
    private var userId: Int? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityEditNoteBinding.inflate(layoutInflater)
        note = intent.getSerializableExtra("note") as CustomerNotesResponseModel.Note
        userId = intent.getIntExtra("description", 0)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.toolbar.findViewById<Button>(R.id.updateBtn).setOnClickListener {
            if (validateForm()) {
                viewModel.updateNote(
                    note.id,
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

        viewModel.noteUpdated.observe(this) {
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
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.task_title, note.id.toString(), "")
        binding.description.editText?.setText(note.description)
    }
}