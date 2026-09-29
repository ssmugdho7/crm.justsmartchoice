package com.divesh.perfex.sales.presentation.proposals.add

import android.app.Activity
import android.content.Context
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.util.Log
import android.view.View
import android.view.inputmethod.InputMethodManager
import android.widget.*
import androidx.activity.viewModels
import androidx.core.view.size
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import com.divesh.perfex.databinding.ActivityAddProposalBinding
import com.divesh.perfex.leads.domain.models.Lead
import com.divesh.perfex.sales.domain.interfaces.AddItemInterface
import com.divesh.perfex.sales.domain.models.item.Item
import com.divesh.perfex.sales.domain.models.proposals.SelectedItem
import com.divesh.perfex.sales.presentation.proposals.partials.AddItemView
import dagger.hilt.android.AndroidEntryPoint
import kotlin.math.roundToLong

@AndroidEntryPoint
class AddProposalActivity : AppCompatActivity(), AddItemInterface {
    private lateinit var binding: ActivityAddProposalBinding
    private val viewModel: AddProposalViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private var relId = 0
    private var relType: String? = ""
    private var relatedToDropDownPosition = -1
    private var relatedToDropDownValues = arrayListOf<String>()
    private var statusDropDownValues = arrayListOf<String>()
    private var currencies = arrayListOf<String>()
    private var countryNames = arrayListOf<String>()
    private var discountType = arrayListOf<String>()
    private var discountModeType = arrayListOf<String>()
    private var items = arrayListOf<String>()
    private var itemsRelatedToId = arrayListOf<Int>()
    private var staffList = arrayListOf<String>()
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddProposalBinding.inflate(layoutInflater)
        setContentView(binding.root)
        initViews()
        setInitialData()
        initListeners()
        observeViewModel()
    }

    private fun initViews() {
        setSupportActionBar(binding.toolbar)
        progressBar = binding.progressBar
        supportActionBar!!.setDisplayShowTitleEnabled(false)
    }

    private fun getLastSelectedItem(): SelectedItem? {
        return viewModel.addedItems.value?.lastOrNull()
    }

    private fun initListeners() {
        binding.toolbarBack.setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.save.setOnClickListener {
            addProposal(null)
        }

        binding.saveAndSend.setOnClickListener {
            addProposal(true)
        }

        binding.relatedTo.setOnItemClickListener { _, _, position, _ ->
            relId = itemsRelatedToId[position]
            if (relType == "lead") {
                if (viewModel.initialProposalResponse.value?.leads != null) {
                    for (lead in viewModel.initialProposalResponse.value?.leads!!) {
                        if (lead != null) {
                            if (lead.id == relId) {
                                fillFormFromLead(lead)
                                break
                            }
                        }
                    }
                }
            } else {
                if (viewModel.initialProposalResponse.value?.customers != null) {
                    for (customer in viewModel.initialProposalResponse.value?.customers!!) {
                        if (customer != null) {
                            if (customer.userid == relId) {
                                fillFormFromCustomer(customer)
                                break
                            }
                        }
                    }
                }
            }
        }

        binding.relatedToType.setOnItemClickListener { _, _, position, _ ->
            onRelatedToTypeSelected(position)
        }

        binding.adjustmentAmount.editText?.addTextChangedListener(object : TextWatcher {
            override fun afterTextChanged(s: Editable?) {
            }

            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {
            }

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                updateDiscountAndAdjustment()
            }
        })

        binding.discountAmount.editText?.addTextChangedListener(object : TextWatcher {
            override fun afterTextChanged(s: Editable?) {
            }

            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {
            }

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                updateDiscountAndAdjustment()
            }
        })

        binding.item.setOnItemClickListener { _, _, position, _ ->
            addItemView(position)
        }
    }

    private fun addProposal(saveAndSend: Boolean?) {
        if (isFormValid()) {
            try {
                val status = when (binding.status.text.toString()) {
                    statusDropDownValues[0] -> {
                        1
                    }
                    statusDropDownValues[1] -> {
                        2
                    }
                    statusDropDownValues[2] -> {
                        3
                    }
                    statusDropDownValues[3] -> {
                        4
                    }
                    statusDropDownValues[4] -> {
                        5
                    }
                    statusDropDownValues[5] -> {
                        5
                    }
                    else -> {
                        1
                    }
                }
                val assigned = getSelectedAssignedStaff()
                val country = getSelectedCountry()
                val selectedQty = getSelectedQtyType()
                val lastSelectedItem = getLastSelectedItem()
                val selectedCurrency = getSelectedCurrency()
                viewModel.addProposal(
                    binding.subject.editText?.text.toString(),
                    relType.toString(),
                    relId,
                    null,
                    binding.date.text.toString(),
                    binding.openTill.text.toString(),
                    selectedCurrency,
                    binding.discountType.text.toString().replace(" ", "_").lowercase(),
                    null,
                    if (binding.allowComments.isChecked) "on" else null,
                    status,
                    assigned,
                    binding.to.editText?.text.toString(),
                    binding.address.editText?.text.toString(),
                    binding.city.editText?.text.toString(),
                    binding.state.editText?.text.toString(),
                    country,
                    binding.zipCode.editText?.text.toString(),
                    binding.email.editText?.text.toString(),
                    binding.phone.editText?.text.toString(),
                    binding.item.text.toString(),
                    selectedQty,
                    lastSelectedItem?.description,
                    lastSelectedItem?.long_description,
                    if (lastSelectedItem?.qty != null) lastSelectedItem.qty else 1,
                    lastSelectedItem?.unit,
                    lastSelectedItem?.rate,
                    if (lastSelectedItem?.taxes?.isNotEmpty() == true) lastSelectedItem.taxes.first()?.name else null,
                    if (viewModel.subTotalAmount.value != null) viewModel.subTotalAmount.value!! else 0.00,
                    if (binding.discountAmount.editText?.text.toString().isNotBlank()) binding.discountAmount.editText?.text.toString().toDouble() else 0.00,
                    if (binding.discountAmountCalculated.text.toString()
                            .isNotBlank()
                    ) binding.discountAmountCalculated.text.toString().toDouble() else 0.00,
                    if (binding.adjustmentAmount.editText?.text.toString()
                            .isNotBlank()
                    ) binding.adjustmentAmount.editText?.text.toString().toDouble() else 0.00,
                    if (viewModel.totalAmount.value.toString().isNotBlank()) {
                        viewModel.totalAmount.value.toString().toDouble()
                    } else 0.00,
                    saveAndSend
                )
            } catch (exception: java.lang.Exception) {
                exception.printStackTrace()
                Log.d(Constants.universalLogTag, exception.message.toString())
            }
        }
    }

    private fun getSelectedCurrency(): Int {
        val currency = binding.currency.text.toString()
        var currencyId = 0
        if (viewModel.initialProposalResponse.value?.currencies?.isNotEmpty() == true) {
            viewModel.initialProposalResponse.value?.currencies?.forEach {
                if (it?.name == currency) {
                    currencyId = it.id
                    return@forEach
                }
            }
        }
        return currencyId
    }

    private fun updateDiscountAndAdjustment() {
        val total = binding.totalAmount.text.toString().toFloat()
        val tax = binding.taxAmount.text.toString().toFloat()
        val discountType = binding.discountType.text.toString()
        if (discountType == getString(R.string.no_discount)) {
            Toast.makeText(this, "${getString(R.string.discount_type)} is required",Toast.LENGTH_SHORT).show()
            return
        }
        val discountGiven = if (binding.discountAmount.editText?.text.toString().isNotBlank()) {
            binding.discountAmount.editText?.text.toString().toDouble()
        } else {
            0.00
        }
        val discountAmount = if (discountGiven > 0) {
            when (discountType) {
                getString(R.string.percentage_symbol) -> {
                    if (discountType == getString(R.string.before_tax)) {
                        ((total - tax) * discountGiven) / 100
                    } else {
                        (total * discountGiven) / 100
                    }
                }
                getString(R.string.fixed_amount) -> {
                    discountGiven
                }
                else -> {
                    0.00
                }
            }
        } else {
            0.00
        }
        val adjustmentAmount =
            if (binding.adjustmentAmount.editText?.text.toString().isNotBlank()) {
                binding.adjustmentAmount.editText?.text.toString().toDouble()
            } else {
                0.00
            }

        binding.adjustment.text = adjustmentAmount.toString()
        binding.discountAmountCalculated.text = discountAmount.toString()
        binding.totalAmount.text =
            ((total.plus(adjustmentAmount).minus(discountAmount)).roundToLong()).toString()
    }

    private fun getSelectedQtyType(): Int {
        return if (binding.radioQty.isChecked) {
            1
        } else if (binding.radioHours.isChecked) {
            2
        } else {
            3
        }
    }

    private fun getSelectedCountry(): Int {
        var countryId = 0
        if (viewModel.initialProposalResponse.value?.countries?.isNotEmpty() == true) {
            val selectedCountry = binding.country.text.toString()
            viewModel.initialProposalResponse.value?.countries!!.forEach {
                if (it?.short_name == selectedCountry) {
                    countryId = it.country_id
                    return@forEach
                }
            }
        }
        return countryId
    }

    private fun getCountryCodeFromId(id: Int): String {
        var code = ""
        if (viewModel.initialProposalResponse.value?.countries?.isNotEmpty() == true) {
            viewModel.initialProposalResponse.value?.countries!!.forEach {
                if (it?.country_id == id) {
                    code = it.short_name.toString()
                    return@forEach
                }
            }
        }
        return code
    }

    private fun getSelectedAssignedStaff(): Int {
        var staffId = 0
        if (viewModel.initialProposalResponse.value?.staff_list?.isNotEmpty() == true) {
            val selectedStaff = binding.assigned.text.toString()
            viewModel.initialProposalResponse.value?.staff_list?.forEach {
                if (selectedStaff == "${it?.firstname} ${it?.lastname}") {
                    staffId = it?.staffid!!
                    return@forEach
                }
            }
        }
        return staffId
    }

    private fun onRelatedToTypeSelected(position: Int) {
        relatedToDropDownPosition = position
        relType = relatedToDropDownValues[relatedToDropDownPosition]
        val itemsRelatedTo = arrayListOf<String>()
        if (position == 0) {
            binding.relatedTo.hint = getString(R.string.search_leads)
            relType = "lead"
            if (viewModel.initialProposalResponse.value?.leads != null) {
                for (lead in viewModel.initialProposalResponse.value?.leads!!) {
                    if (lead != null) {
                        itemsRelatedTo.add("${lead.name} - ${lead.company}")
                        itemsRelatedToId.add(lead.id)
                    }
                }
            }
        } else {
            binding.relatedTo.hint = getString(R.string.search_customers)
            relType = "customer"
            if (viewModel.initialProposalResponse.value?.customers != null) {
                for (customer in viewModel.initialProposalResponse.value?.customers!!) {
                    if (customer != null) {
                        customer.company?.let { it1 -> itemsRelatedTo.add(it1) }
                        itemsRelatedToId.add(customer.userid)
                    }
                }
            }
        }
        val adapter =
            ArrayAdapter(this@AddProposalActivity, R.layout.list_item, itemsRelatedTo)
        binding.relatedTo.setAdapter(adapter)
    }

    private fun fillFormFromCustomer(customer: CustomerResponseModel.Data.Client) {
        binding.to.editText?.setText(customer.company)
        binding.address.editText?.setText(customer.address)
        binding.city.editText?.setText(customer.city)
        binding.state.editText?.setText(customer.state)
        binding.zipCode.editText?.setText(customer.zip)
        binding.country.setText(getCountryCodeFromId(customer.country.toString().toInt()), false)
        binding.phone.editText?.setText(customer.phonenumber)
    }

    private fun fillFormFromLead(lead: Lead) {
        binding.to.editText?.setText(lead.company)
        binding.address.editText?.setText(lead.address)
        binding.city.editText?.setText(lead.city)
        binding.state.editText?.setText(lead.state)
        binding.zipCode.editText?.setText(lead.zip)
        binding.country.setText(getCountryCodeFromId(lead.country.toString().toInt()), false)
        binding.phone.editText?.setText(lead.phonenumber)
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

        viewModel.internetProblem.observe(this) {
            if (it != null) {
                Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
            }
        }

        viewModel.proposalAdded.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.initialProposalResponse.observe(this) {
            if (it.currencies != null) {
                if (it.currencies!!.isNotEmpty()) {
                    it.currencies!!.forEach { currency ->
                        if (currency != null) {
                            currencies.add(currency.name)
                        }
                    }
                    val currencyAdapter =
                        ArrayAdapter(this@AddProposalActivity, R.layout.list_item, currencies)
                    binding.currency.setAdapter(currencyAdapter)
                }
            }

            if (it.countries != null) {
                if (it.countries!!.isNotEmpty()) {
                    it.countries!!.forEach { country ->
                        country?.short_name?.let { it1 -> countryNames.add(it1) }
                    }
                    val countryAdapter = ArrayAdapter(this@AddProposalActivity, R.layout.list_item, countryNames)
                    binding.country.setAdapter(countryAdapter)
                }
            }

            if (it.itemData != null) {
                if (it.itemData?.items != null) {
                    it.itemData?.items?.forEach { item ->
                        item.description.let { description ->
                            if (description != null) {
                                items.add(description)
                            }
                        }
                    }
                    val itemsAdapter =
                        ArrayAdapter(this@AddProposalActivity, R.layout.list_item, items)
                    binding.item.setAdapter(itemsAdapter)
                }
            }

            if (it.staff_list != null) {
                if (it.staff_list?.isNotEmpty() == true) {
                    it.staff_list?.forEach { staff ->
                        staffList.add("${staff?.firstname} ${staff?.lastname}")
                    }
                    val staffAdapter =
                        ArrayAdapter(this@AddProposalActivity, R.layout.list_item, staffList)
                    binding.assigned.setAdapter(staffAdapter)
                }
            }

            if (relType == "customer") {
                onRelatedToTypeSelected(1)
            } else {
                onRelatedToTypeSelected(0)
            }
        }

        viewModel.subTotalAmount.observe(this) {
            if (it != null) {
                binding.subTotalAmount.text = it.toString()
            }
        }

        viewModel.totalTaxAmount.observe(this) {
            if (it != null) {
                binding.taxAmount.text = it.toString()
            }
        }

        viewModel.totalAmount.observe(this) {
            if (it != null) {
                binding.totalAmount.text = it.toString()
            }
        }

        viewModel.addedItems.observe(this) {
            Log.d(Constants.universalLogTag, it.toString())
        }
    }

    private fun isFormValid(): Boolean {
        var isValid = true
        if (relType.isNullOrEmpty()) {
            Toast.makeText(
                this,
                "${getString(R.string.related_to_type)} is required",
                Toast.LENGTH_SHORT
            ).show()
            isValid = false
        }
        if (binding.date.text?.toString()?.isEmpty() == true) {
            binding.date.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.email.editText?.text?.toString()?.isEmpty() == true) {
            binding.email.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.to.editText?.text?.toString()?.isEmpty() == true) {
            binding.date.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.currency.text?.toString()?.isEmpty() == true) {
            binding.currency.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.subject.editText?.text?.toString()?.isEmpty() == true) {
            binding.subject.error = getString(R.string.field_required)
            isValid = false
        }
        if (viewModel.initialProposalResponse.value?.itemData?.items?.size == 0) {
            Toast.makeText(this, "${getString(R.string.item)} is required", Toast.LENGTH_SHORT)
                .show()
            isValid = false
        }
        return isValid
    }

    private fun setInitialData() {
        relId = intent.getIntExtra("relId", 0)
        relType = intent.getStringExtra("relType").toString()
        relatedToDropDownValues = arrayListOf(
            getString(R.string.related_to_lead), getString(R.string.related_to_customer)
        )
        statusDropDownValues = arrayListOf(
            getString(R.string.status_open), getString(R.string.status_declined), getString(
                R.string.status_accepted
            ), getString(R.string.status_sent), getString(R.string.status_revised), getString(
                R.string.status_draft
            )
        )
        discountType = arrayListOf(
            getString(R.string.no_discount),
            getString(R.string.before_tax),
            getString(R.string.after_tax)
        )
        discountModeType =
            arrayListOf(getString(R.string.percentage_symbol), getString(R.string.fixed_amount))
        binding.relatedToType.setAdapter(
            ArrayAdapter(
                this, R.layout.list_item, relatedToDropDownValues
            )
        )
        binding.status.setAdapter(
            ArrayAdapter(
                this,
                R.layout.list_item,
                statusDropDownValues
            )
        )
        binding.discountType.setAdapter(ArrayAdapter(this, R.layout.list_item, discountType))
        binding.discountMode.setAdapter(
            ArrayAdapter(
                this,
                R.layout.list_item,
                discountModeType
            )
        )
        Extensions().transformIntoDatePicker(binding.date, this, null, null, "yyyy-MM-dd")
        Extensions().transformIntoDatePicker(binding.openTill, this, null, null, "yyyy-MM-dd")
    }

    private fun addItemView(selectedItemPosition: Int) {
        var item: Item? = null
        viewModel.initialProposalResponse.value?.itemData?.items?.forEachIndexed { index, proposalItem ->
            if (index == selectedItemPosition) {
                item = proposalItem
                return@forEachIndexed
            }
        }
        val size = if (viewModel.addedItems.value != null) {
            viewModel.addedItems.value!!.size
        } else {
            0
        }
        val addProposalItemView = AddItemView(
            this,
            viewModel.initialProposalResponse.value?.itemData?.taxes,
            item,
            size,
            this
        )
        if (binding.dynamicItemsView.size > 0)
            binding.dynamicItemsView.addView(addProposalItemView.getSpaceView())
        binding.dynamicItemsView.addView(addProposalItemView.getView())
        hideKeyboard(currentFocus ?: View(this))
        Toast.makeText(this, getString(R.string.item_added), Toast.LENGTH_SHORT).show()
        binding.scrollView.post {
            binding.scrollView.fullScroll(View.FOCUS_DOWN)
        }
    }

    private fun Context.hideKeyboard(view: View) {
        val inputMethodManager =
            getSystemService(Activity.INPUT_METHOD_SERVICE) as InputMethodManager
        inputMethodManager.hideSoftInputFromWindow(view.windowToken, 0)
    }

    override fun onItemAdded(item: SelectedItem, index: Int) {
        viewModel.addOrUpdateItem(item, index, "add")
    }

    override fun onItemUpdated(item: SelectedItem, index: Int) {
        viewModel.addOrUpdateItem(item, index, "update")
    }

    override fun onItemRemoved(item: SelectedItem, index: Int) {
        viewModel.addOrUpdateItem(item, index, "remove")
    }
}
