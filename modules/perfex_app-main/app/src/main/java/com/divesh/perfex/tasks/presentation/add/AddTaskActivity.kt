package com.divesh.perfex.tasks.presentation.add

import android.app.DatePickerDialog
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.*
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddLeadTaskBinding
import dagger.hilt.android.AndroidEntryPoint
import java.util.*

@AndroidEntryPoint
class AddTaskActivity : AppCompatActivity(), DatePickerDialog.OnDateSetListener {
    private lateinit var binding: ActivityAddLeadTaskBinding
    private var day = 0; private var month:Int = 0; private var year:Int = 0
    private lateinit var progressBar: ProgressBar
    private var selectingDateType = "startDate"
    private var relId = 0
    private var relType : String? = ""
    private val viewModel: AddTaskViewModel by viewModels()
    private var relatedToDropDownPosition = -1
    private var relatedToDropDownValues = arrayListOf<String>()
    private var itemsRelatedToId = arrayListOf<Int>()
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddLeadTaskBinding.inflate(layoutInflater)
        val view: View = binding.root
        setContentView(view)

        setSupportActionBar(binding.toolbar)
        setInitialData()
        progressBar = binding.progressBar
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener{
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarAdd).setOnClickListener{
            if(!validateForm()){
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
                viewModel.addTask(repeatEvery, priority, binding.billable.isChecked, binding.subject.editText?.text.toString(), binding.hourlyRate.editText?.text.toString(), binding.startDate.editText?.text.toString(), binding.dueDate.editText?.text.toString(), binding.repeatEveryCustomNumber.editText?.text.toString(), binding.repeatEveryCustomType.text.toString(), binding.totalCycles.editText?.text.toString(), relType, relId, binding.description.editText?.text.toString())
            }else{
                Toast.makeText(this, "Please fill mandatory fields" , Toast.LENGTH_SHORT).show()
            }
        }
        observeViewModel()
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

        viewModel.taskAdded.observe(this){
            if(it){
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.relatedToResponse.observe(this){
            val value = relatedToDropDownValues[relatedToDropDownPosition]
            val itemsRelatedTo = arrayListOf<String>()
            if(value == "Lead"){
                relType = "lead"
                for(lead in it.leads){
                    itemsRelatedTo.add("${lead.name} - ${lead.company}")
                    itemsRelatedToId.add(lead.id)
                }
            }else if(value == "Customer"){
                relType = "customer"
                for(customer in it.customers){
                    customer.company?.let { it1 -> itemsRelatedTo.add(it1) }
                    itemsRelatedToId.add(customer.userid)
                }
            }
            val adapter = ArrayAdapter(this@AddTaskActivity, R.layout.list_item, itemsRelatedTo)
            binding.relatedTo.setAdapter(adapter)
        }
    }

    private fun validateForm(): Boolean {
        var hasError = false
        if(binding.priority.text.isEmpty()){
            binding.priorityParent.error = getString(R.string.field_required)
            hasError = true
        }
        if(binding.subject.editText?.text?.isEmpty() == true){
            binding.subject.error = getString(R.string.field_required)
            hasError = true
        }
        if(binding.hourlyRate.editText?.text?.isEmpty() == true){
            binding.hourlyRate.error = getString(R.string.field_required)
            hasError = true
        }
        if(binding.startDate.editText?.text?.isEmpty() == true){
            binding.startDate.error = getString(R.string.field_required)
            hasError = true
        }
        return hasError
    }

    private fun setInitialData() {
        //set title first
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(R.string.add_task)
        val priorityDropdownValues = arrayListOf(getString(R.string.low_priority) , getString(R.string.medium_priority), getString(
            R.string.high_priority), getString(R.string.urgent_priority))
        relatedToDropDownValues = arrayListOf(getString(R.string.related_to_lead) , getString(R.string.related_to_customer))
        val repeatEveryDropDownValues = arrayListOf(getString(R.string.one_week) , getString(R.string.two_weeks), getString(
            R.string.one_month), getString(R.string.two_months), getString(R.string.three_months), getString(
            R.string.six_months), getString(R.string.one_year), getString(R.string.custom_repeat))
        val repeatEveryCustomTypeDropDownValues = arrayListOf(getString(R.string.days) , getString(R.string.weeks), getString(
            R.string.months), getString(R.string.years))

        relId = intent.getIntExtra("relId", 0)
        relType = intent.getStringExtra("relType").toString()

        if(relId == 0){
            binding.relatedToLayout.visibility = View.VISIBLE
            binding.relatedToTypeLayout.visibility = View.VISIBLE
        }else{
            binding.relatedToLayout.visibility = View.GONE
            binding.relatedToTypeLayout.visibility = View.GONE
        }

        binding.priority.setAdapter(ArrayAdapter(this, R.layout.list_item, priorityDropdownValues))
        binding.relatedToType.setAdapter(ArrayAdapter(this, R.layout.list_item, relatedToDropDownValues))
        binding.repeatEvery.setAdapter(ArrayAdapter(this, R.layout.list_item, repeatEveryDropDownValues))
        binding.repeatEveryCustomType.setAdapter(ArrayAdapter(this, R.layout.list_item, repeatEveryCustomTypeDropDownValues))

        binding.repeatEvery.setOnItemClickListener{ _, _, position, _ ->
            val value = repeatEveryDropDownValues[position]
            if(value == getString(R.string.custom_repeat)){
                binding.repeatEveryCustomNumber.visibility = View.VISIBLE
                binding.repeatEveryCustomTypeLayout.visibility = View.VISIBLE
            }else{
                binding.repeatEveryCustomNumber.visibility = View.GONE
                binding.repeatEveryCustomTypeLayout.visibility = View.GONE
                binding.totalCycles.visibility = View.GONE
            }
        }
        binding.relatedToType.setOnItemClickListener{ _, _, position, _ ->
            relatedToDropDownPosition = position
            viewModel.getRelatedToItems()
            relType = relatedToDropDownValues[relatedToDropDownPosition]
        }
        binding.startDate.editText?.setOnClickListener{
            selectingDateType = "startDate"
            val calendar: Calendar = Calendar.getInstance()
            year = calendar.get(Calendar.YEAR)
            month = calendar.get(Calendar.MONTH)
            day = calendar.get(Calendar.DAY_OF_MONTH)
            val datePickerDialog =
                DatePickerDialog(this, this, year, month, day)
            datePickerDialog.datePicker.minDate = System.currentTimeMillis() - 1000
            datePickerDialog.show()
        }
        binding.dueDate.editText?.setOnClickListener{
            selectingDateType = "endDate"
            val calendar: Calendar = Calendar.getInstance()
            year = calendar.get(Calendar.YEAR)
            month = calendar.get(Calendar.MONTH)
            day = calendar.get(Calendar.DAY_OF_MONTH)
            val datePickerDialog =
                DatePickerDialog(this, this, year, month, day)
            datePickerDialog.show()
        }
        binding.relatedTo.setOnItemClickListener { _, _, position, _ ->
            relId = itemsRelatedToId[position]
        }
    }

    override fun onDateSet(view: DatePicker?, year: Int, month: Int, dayOfMonth: Int) {
        var monthString = month.toString(); var dayString = dayOfMonth.toString();  val yearString = year.toString()
        if(month < 10)
            monthString = "0$month"
        if(dayOfMonth < 10)
            dayString = "0$dayOfMonth"

        if(selectingDateType == "startDate"){
            binding.startDate.editText?.setText(getString(R.string.date_time_string , yearString, monthString , dayString))
        }else{
            binding.dueDate.editText?.setText(getString(R.string.date_time_string , yearString, monthString , dayString))
        }
    }
}