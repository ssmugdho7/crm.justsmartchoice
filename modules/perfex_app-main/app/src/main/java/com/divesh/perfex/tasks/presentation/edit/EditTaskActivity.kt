package com.divesh.perfex.tasks.presentation.edit

import android.app.DatePickerDialog
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import androidx.core.text.parseAsHtml
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityEditTaskBinding
import dagger.hilt.android.AndroidEntryPoint
import java.util.*

@AndroidEntryPoint class EditTaskActivity : AppCompatActivity(), DatePickerDialog.OnDateSetListener {
    private lateinit var binding: ActivityEditTaskBinding
    private var taskId = ""
    private var day = 0
    private var month: Int = 0
    private var year: Int = 0
    private lateinit var progressBar: ProgressBar
    private var selectingDateType = "startDate"
    private lateinit var relId: String
    private lateinit var relType: String
    private lateinit var recurring: String
    private lateinit var relName: String
    private val viewModel: EditLeadTaskViewModel by viewModels()
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityEditTaskBinding.inflate(layoutInflater)
        val view: View = binding.root
        setContentView(view)

        setSupportActionBar(binding.toolbar)
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarUpdate).setOnClickListener {
            if (!validateForm()) {
                var billable: String? = "on"
                var repeatEvery = ""
                var priority = ""
                when (binding.repeatEvery.text.toString()) {
                    getString(R.string.one_week) -> {
                        repeatEvery = "1-week"
                    }
                    getString(R.string.two_weeks) -> {
                        repeatEvery = "2-week"
                    }
                    getString(R.string.one_month) -> {
                        repeatEvery = "1-month"
                    }
                    getString(R.string.two_months) -> {
                        repeatEvery = "2-month"
                    }
                    getString(R.string.three_months) -> {
                        repeatEvery = "3-month"
                    }
                    getString(R.string.six_months) -> {
                        repeatEvery = "6-month"
                    }
                    getString(R.string.one_year) -> {
                        repeatEvery = "1-year"
                    }
                    getString(R.string.custom_repeat) -> {
                        repeatEvery = "custom"
                    }
                }
                when (binding.priority.text.toString()) {
                    getString(R.string.low_priority) -> {
                        priority = "1"
                    }
                    getString(R.string.medium_priority) -> {
                        priority = "2"
                    }
                    getString(R.string.high_priority) -> {
                        priority = "3"
                    }
                    getString(R.string.urgent_priority) -> {
                        priority = "4"
                    }
                }

                if (!binding.billable.isChecked) billable = null
                viewModel.updateTask(
                    repeatEvery,
                    priority,
                    billable,
                    binding.subject.editText?.text.toString(),
                    binding.hourlyRate.editText?.text.toString(),
                    binding.startDate.editText?.text.toString(),
                    binding.dueDate.editText?.text.toString(),
                    binding.repeatEveryCustomNumber.editText?.text.toString(),
                    binding.repeatEveryCustomType.text.toString(),
                    binding.totalCycles.editText?.text.toString(),
                    relType,
                    relId,
                    binding.description.editText?.text.toString(),
                    taskId.toInt()
                )
            }
        }

        observeViewModel()
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

        viewModel.taskUpdated.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }
    }

    private fun validateForm(): Boolean {
        var hasError = false
        if (binding.priority.text.isEmpty()) {
            binding.priorityParent.error = getString(R.string.field_required)
            hasError = true
        }
        if (binding.subject.editText?.text?.isEmpty() == true) {
            binding.subject.error = getString(R.string.field_required)
            hasError = true
        }
        if (binding.hourlyRate.editText?.text?.isEmpty() == true) {
            binding.hourlyRate.error = getString(R.string.field_required)
            hasError = true
        }
        if (binding.startDate.editText?.text?.isEmpty() == true) {
            binding.startDate.error = getString(R.string.field_required)
            hasError = true
        }
        return hasError
    }

    private fun setInitialData() {
//        val status = intent.getStringExtra("STATUS").toString()
//        val billed = intent.getIntExtra("billed", 0)
        val billable = intent.getIntExtra("billable", 0)
        val cycles = intent.getStringExtra("cycles").toString()
        val dueDate:String? = intent.getStringExtra("duedate")
        val hourlyRate = intent.getStringExtra("hourly_rate").toString()
        taskId = intent.getStringExtra("id").toString()
        val priority = intent.getStringExtra("priority").toString()
        val recurringType = intent.getStringExtra("recurring_type").toString()
        relId = intent.getStringExtra("rel_id").toString()
        relType = intent.getStringExtra("rel_type").toString()
        val repeatEvery = intent.getStringExtra("repeat_every").toString()
        val startDate = intent.getStringExtra("startdate").toString()
        val taskName = intent.getStringExtra("task_name").toString()
        val description = intent.getStringExtra("description").toString()
        val customRecurring = intent.getStringExtra("custom_recurring").toString()
        recurring = intent.getStringExtra("recurring").toString()
        relName = intent.getStringExtra("rel_name").toString()

        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(
            R.string.task_title, taskId, taskName
        )

        //set form data
        if(taskName.isNotBlank())
            binding.subject.editText?.setText(taskName.parseAsHtml())
        binding.hourlyRate.editText?.setText(hourlyRate)
        binding.startDate.editText?.setText(startDate)
        if(!dueDate.isNullOrBlank())
            binding.dueDate.editText?.setText(dueDate)
        binding.totalCycles.editText?.setText(cycles)
        if(description.isNotBlank())
            binding.description.editText?.setText(description.parseAsHtml())
        binding.repeatEveryCustomNumber.editText?.setText(repeatEvery)
        binding.billable.isChecked = billable == 1

//        val statusDropdownValues = arrayListOf(getString(R.string.not_started) , getString(R.string.waiting_feedback), getString(R.string.testing), getString(R.string.in_progress), getString(R.string.complete))
        val priorityDropdownValues = arrayListOf(
            getString(R.string.low_priority),
            getString(R.string.medium_priority),
            getString(R.string.high_priority),
            getString(R.string.urgent_priority)
        )
        val repeatEveryDropDownValues = arrayListOf(
            getString(R.string.one_week),
            getString(R.string.two_weeks),
            getString(R.string.one_month),
            getString(R.string.two_months),
            getString(R.string.three_months),
            getString(R.string.six_months),
            getString(R.string.one_year),
            getString(R.string.custom_repeat)
        )
        val repeatEveryCustomTypeDropDownValues = arrayListOf(
            getString(R.string.days),
            getString(R.string.weeks),
            getString(R.string.months),
            getString(R.string.years)
        )

//        binding.status.setAdapter(ArrayAdapter(this, R.layout.list_item, statusDropdownValues))
        binding.priority.setAdapter(ArrayAdapter(this, R.layout.list_item, priorityDropdownValues))
        binding.repeatEvery.setAdapter(
            ArrayAdapter(
                this, R.layout.list_item, repeatEveryDropDownValues
            )
        )
        binding.repeatEveryCustomType.setAdapter(
            ArrayAdapter(
                this, R.layout.list_item, repeatEveryCustomTypeDropDownValues
            )
        )

        when (priority) {
            "1" -> {
                binding.priority.setText(getString(R.string.low_priority), false)
            }
            "2" -> {
                binding.priority.setText(getString(R.string.medium_priority), false)
            }
            "3" -> {
                binding.priority.setText(getString(R.string.high_priority), false)
            }
            "4" -> {
                binding.priority.setText(getString(R.string.urgent_priority), false)
            }
        }
        /*when (status) {
            "1" -> {
                binding.status.setText(getString(R.string.not_started), false)
            }
            "2" -> {
                binding.status.setText(getString(R.string.waiting_feedback), false)
            }
            "3" -> {
                binding.status.setText(getString(R.string.testing), false)
            }
            "4" -> {
                binding.status.setText(getString(R.string.in_progress), false)
            }
            "5" -> {
                binding.status.setText(getString(R.string.complete), false)
            }
        }*/

        if (customRecurring == "1") {
            binding.repeatEveryCustomNumber.visibility = View.VISIBLE
            binding.repeatEveryCustomTypeLayout.visibility = View.VISIBLE
            when (recurringType) {
                "week" -> {
                    binding.repeatEveryCustomType.setText(getString(R.string.weeks), false)
                }
                "day" -> {
                    binding.repeatEveryCustomType.setText(getString(R.string.days), false)
                }
                "month" -> {
                    binding.repeatEveryCustomType.setText(getString(R.string.months), false)
                }
                "year" -> {
                    binding.repeatEveryCustomType.setText(getString(R.string.years), false)
                }
            }
            binding.repeatEvery.setText(getString(R.string.custom_repeat), false)
        } else {
            binding.repeatEveryCustomNumber.visibility = View.GONE
            binding.repeatEveryCustomTypeLayout.visibility = View.GONE
            when (recurringType) {
                "week" -> {
                    if (repeatEvery == "1") binding.repeatEvery.setText(
                        getString(R.string.one_week), false
                    )
                    else if (repeatEvery == "2") binding.repeatEvery.setText(
                        getString(R.string.two_weeks), false
                    )
                }
                "month" -> {
                    when (repeatEvery) {
                        "1" -> binding.repeatEvery.setText(getString(R.string.one_month), false)
                        "2" -> binding.repeatEvery.setText(getString(R.string.two_months), false)
                        "3" -> binding.repeatEvery.setText(getString(R.string.three_months), false)
                        "6" -> binding.repeatEvery.setText(getString(R.string.six_months), false)
                    }
                }
                "year" -> {
                    if (repeatEvery == "1") binding.repeatEvery.setText(
                        getString(R.string.one_year), false
                    )
                }
                "custom" -> {
                    binding.repeatEvery.setText(getString(R.string.custom_repeat), false)
                }
                else -> {
                    binding.repeatEveryCustomNumber.visibility = View.GONE
                    binding.repeatEveryCustomTypeLayout.visibility = View.GONE
                }
            }
        }

        binding.repeatEvery.setOnItemClickListener { _, _, position, _ ->
            val value = repeatEveryDropDownValues[position]
            if (value == getString(R.string.custom_repeat)) {
                binding.repeatEveryCustomNumber.visibility = View.VISIBLE
                binding.repeatEveryCustomTypeLayout.visibility = View.VISIBLE
            } else {
                binding.repeatEveryCustomNumber.visibility = View.GONE
                binding.repeatEveryCustomTypeLayout.visibility = View.GONE
                binding.totalCycles.visibility = View.GONE
            }
        }
        binding.startDate.editText?.setOnClickListener {
            selectingDateType = "startDate"
            val calendar: Calendar = Calendar.getInstance()
            year = calendar.get(Calendar.YEAR)
            month = calendar.get(Calendar.MONTH)
            day = calendar.get(Calendar.DAY_OF_MONTH)
            val datePickerDialog = DatePickerDialog(this, this, year, month, day)
            datePickerDialog.datePicker.minDate = System.currentTimeMillis() - 1000
            datePickerDialog.show()
        }
        binding.dueDate.editText?.setOnClickListener {
            selectingDateType = "endDate"
            val calendar: Calendar = Calendar.getInstance()
            year = calendar.get(Calendar.YEAR)
            month = calendar.get(Calendar.MONTH)
            day = calendar.get(Calendar.DAY_OF_MONTH)
            val datePickerDialog = DatePickerDialog(this, this, year, month, day)
            datePickerDialog.show()
        }
    }

    override fun onDateSet(view: DatePicker?, year: Int, month: Int, dayOfMonth: Int) {
        var monthString = month.toString()
        var dayString = dayOfMonth.toString()
        val yearString = year.toString()
        if (month < 10) monthString = "0$month"
        if (dayOfMonth < 10) dayString = "0$dayOfMonth"

        if (selectingDateType == "startDate") {
            binding.startDate.editText?.setText(
                getString(
                    R.string.date_time_string, yearString, monthString, dayString
                )
            )
        } else {
            binding.dueDate.editText?.setText(
                getString(
                    R.string.date_time_string, yearString, monthString, dayString
                )
            )
        }
    }
}