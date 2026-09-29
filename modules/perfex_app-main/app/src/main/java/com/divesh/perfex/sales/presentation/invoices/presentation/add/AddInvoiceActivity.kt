package com.divesh.perfex.sales.presentation.invoices.presentation.add

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Context
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.util.Log
import android.view.View
import android.view.inputmethod.InputMethodManager
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.viewModels
import androidx.core.view.size
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityAddInvoicesBinding
import com.divesh.perfex.sales.domain.interfaces.AddItemInterface
import com.divesh.perfex.sales.domain.interfaces.AddressUpdatedInterface
import com.divesh.perfex.sales.domain.models.AddressPickerData
import com.divesh.perfex.sales.domain.models.invoices.InitialInvoiceResponseModel
import com.divesh.perfex.sales.domain.models.item.Item
import com.divesh.perfex.sales.domain.models.proposals.SelectedItem
import com.divesh.perfex.sales.presentation.invoices.presentation.manage.ManageInvoicesViewModel
import com.divesh.perfex.sales.presentation.invoices.presentation.utils.AddressPickerBottomSheet
import com.divesh.perfex.sales.presentation.proposals.partials.AddItemView
import dagger.hilt.android.AndroidEntryPoint
import kotlin.math.roundToLong

@AndroidEntryPoint
class AddInvoiceActivity : AppCompatActivity(), AddItemInterface, AddressUpdatedInterface {
    private lateinit var binding: ActivityAddInvoicesBinding
    private val viewModel: ManageInvoicesViewModel by viewModels()
    private val extensions: Extensions = Extensions()
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityAddInvoicesBinding.inflate(layoutInflater)
        setContentView(binding.root)
        initViews()
        setInitialData()
        initListeners()
        observeViewModel()
    }

    private fun initViews() {
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
    }

    private fun initListeners() {
        binding.toolbarBack.setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }

        binding.save.setOnClickListener {
            addInvoice(false)
        }

        binding.saveAndSend.setOnClickListener {
            addInvoice(true)
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

        binding.billTo.setOnClickListener {
            transformIntoAddressPicker()
        }

        binding.shipTo.setOnClickListener {
            transformIntoAddressPicker()
        }
    }

    private fun transformIntoAddressPicker() {
        val addressPickerBottomSheet =
            AddressPickerBottomSheet(
                this,
                viewModel.initialInvoiceResponse.value?.countries,
                viewModel.addressPickerData
            )
        addressPickerBottomSheet.show(supportFragmentManager, "addressPickerBottomSheet")
    }

    private fun addInvoice(saveAndSend: Boolean) {
        if (isFormValid()) {
            try {
                viewModel.addInvoice(
                   "on",
                    binding.customer.text.toString(),
                    null,
                    binding.invoiceNumber.editText?.text.toString(),
                    binding.invoiceDate.text.toString(),
                    binding.dueDate.text.toString(),
                    binding.currency.text.toString(),
                    binding.saleAgent.text.toString(),
                    binding.recurringInvoice.text.toString(),
                    binding.discountType.text.toString().replace(" ", "_").lowercase(),
                    binding.adminNote.editText?.text.toString(),
                    binding.item.text.toString(),
                    if (viewModel.subTotalAmount.value != null) viewModel.subTotalAmount.value!! else 0.00,
                    binding.discountMode.text.toString(),
                    if (binding.discountAmount.editText?.text.toString().isNotBlank()) binding.discountAmount.editText?.text.toString().toDouble() else 0.00,
                    if (binding.discountAmountCalculated.text.toString().isNotBlank()) binding.discountAmountCalculated.text.toString().toDouble() else 0.00,
                    if (binding.adjustmentAmount.editText?.text.toString().isNotBlank()) binding.adjustmentAmount.editText?.text.toString().toDouble() else 0.00,
                    if (viewModel.totalAmount.value.toString().isNotBlank()) {
                        viewModel.totalAmount.value.toString().toDouble()
                    } else 0.00,
                    binding.termsAndConditions.editText?.text.toString(),
                    binding.clientNote.editText?.text.toString(),
                    saveAndSend
                )
            } catch (exception: java.lang.Exception) {
                exception.printStackTrace()
                Log.d(Constants.universalLogTag, exception.message.toString())
            }
        }
    }

    private fun updateDiscountAndAdjustment() {
        val total = binding.totalAmount.text.toString().toFloat()
        val tax = binding.taxAmount.text.toString().toFloat()
        val discountType = binding.discountType.text.toString()
        if (discountType == getString(R.string.no_discount)) {
            Toast.makeText(
                this,
                "${getString(R.string.discount_type)} is required",
                Toast.LENGTH_SHORT
            ).show()
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

    private fun observeViewModel() {
        viewModel.loading.observe(this) {
            if (it) {
                binding.progressBar.visibility = View.VISIBLE
            } else {
                binding.progressBar.visibility = View.GONE
            }
        }

        viewModel.responseMessage.observe(this) {
            if (it != null) {
                extensions.showMessage(binding.root, it)
                binding.progressBar.visibility = View.GONE
            }
        }

        viewModel.loadError.observe(this) {
            if (it != null) {
                extensions.showMessage(binding.root, it)
                binding.progressBar.visibility = View.GONE
            }
        }

        viewModel.internetProblem.observe(this) {
            if (it != null) {
                Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
                binding.progressBar.visibility = View.GONE
            }
        }

        viewModel.itemAdded.observe(this) {
            if (it) {
                onBackPressedDispatcher.onBackPressed()
                finish()
            }
        }

        viewModel.initialInvoiceResponse.observe(this) {
            if (it != null) {
                if (it.currencies != null) {
                    if (it.currencies.isNotEmpty()) {
                        it.currencies.forEach { currency ->
                            currency?.name?.let { it1 -> viewModel.currencies.add(it1) }
                        }
                        val currencyAdapter = ArrayAdapter(
                            this@AddInvoiceActivity,
                            R.layout.list_item,
                            viewModel.currencies
                        )
                        binding.currency.setAdapter(currencyAdapter)
                    }
                }

                if (it.customers != null) {
                    if (it.customers.isNotEmpty()) {
                        it.customers.forEach { customer ->
                            if (customer != null) {
                                customer.company?.let { it1 -> viewModel.customers.add(it1) }
                            }
                        }
                        val customersAdapter = ArrayAdapter(
                            this@AddInvoiceActivity,
                            R.layout.list_item,
                            viewModel.customers
                        )
                        binding.customer.setAdapter(customersAdapter)
                    }
                }

                if (it.payment_modes != null) {
                    if (it.payment_modes.isNotEmpty()) {
                        it.payment_modes.forEach { paymentMode ->
                            if (paymentMode != null) {
                                viewModel.paymentModes += paymentMode.name
                            }
                        }
                        extensions.transformIntoMultiSelectPicker(
                            binding.paymentModes,
                            this,
                            viewModel.paymentModes,
                            BooleanArray(viewModel.paymentModes.size),
                            getString(R.string.payment_mode)
                        ) { selectedPositions ->
                            val selectedPaymentModesString = StringBuilder()
                            val selectedPaymentModes =
                                BooleanArray(viewModel.paymentModes.size)
                            var selectedModes: List<InitialInvoiceResponseModel.PaymentMode?> =
                                arrayListOf()
                            selectedPositions.forEachIndexed { index, taxPosition ->
                                selectedPaymentModesString.append(
                                    if ((index + 1) == selectedPositions.size) {
                                        viewModel.paymentModes[taxPosition]
                                    } else {
                                        "${viewModel.paymentModes[taxPosition]},"
                                    }
                                )
                                selectedPaymentModes[index] = true
                            }
                            binding.paymentModes.text = selectedPaymentModesString.toString()
                            it.payment_modes.forEachIndexed { index: Int, paymentMode: InitialInvoiceResponseModel.PaymentMode? ->
                                if (selectedPositions.contains(index)) {
                                    if(paymentMode?.id != null)
                                        viewModel.selectedPaymentModeIds.add(paymentMode.id)
                                    selectedModes = selectedModes + paymentMode
                                }
                            }
                        }
                    }
                }

                if (it.item_data != null) {
                    it.item_data.items.forEach { item ->
                        item.description.let { description ->
                            if (description != null) {
                                viewModel.items.add(description)
                            }
                        }
                    }
                    val itemsAdapter =
                        ArrayAdapter(this@AddInvoiceActivity, R.layout.list_item, viewModel.items)
                    binding.item.setAdapter(itemsAdapter)
                }

                if (it.staff_list != null) {
                    if (it.staff_list.isNotEmpty()) {
                        it.staff_list.forEach { staff ->
                            viewModel.staffList.add("${staff?.firstname} ${staff?.lastname}")
                        }
                        val staffAdapter =
                            ArrayAdapter(
                                this@AddInvoiceActivity,
                                R.layout.list_item,
                                viewModel.staffList
                            )
                        binding.saleAgent.setAdapter(staffAdapter)
                    }
                }
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
        viewModel.loadInitialInvoiceData()
    }

    private fun isFormValid(): Boolean {
        var isValid = true

        if (binding.invoiceDate.text?.toString()?.isEmpty() == true) {
            binding.invoiceDate.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.invoiceNumber.editText?.text?.toString()?.isEmpty() == true) {
            binding.invoiceNumber.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.currency.text?.toString()?.isEmpty() == true) {
            binding.currency.error = getString(R.string.field_required)
            isValid = false
        }
        if (binding.paymentModes.text?.toString()?.isEmpty() == true) {
            binding.paymentModes.error = getString(R.string.field_required)
            isValid = false
        }
        if (viewModel.initialInvoiceResponse.value?.item_data?.items?.size == 0) {
            Toast.makeText(this, "${getString(R.string.item)} is required", Toast.LENGTH_SHORT)
                .show()
            isValid = false
        }
        return isValid
    }

    @SuppressLint("StringFormatInvalid")
    private fun setInitialData() {
        viewModel.discountType = arrayListOf(
            getString(R.string.no_discount),
            getString(R.string.before_tax),
            getString(R.string.after_tax)
        )

        viewModel.recurringInvoiceModes = arrayListOf(
            getString(R.string.no),
            getString(R.string.every_1_month),
            getString(R.string.every_x_month, "2"),
            getString(R.string.every_x_month, "3"),
            getString(R.string.every_x_month, "4"),
            getString(R.string.every_x_month, "5"),
            getString(R.string.every_x_month, "6"),
            getString(R.string.every_x_month, "7"),
            getString(R.string.every_x_month, "8"),
            getString(R.string.every_x_month, "9"),
            getString(R.string.every_x_month, "10"),
            getString(R.string.every_x_month, "11"),
            getString(R.string.every_x_month, "12"),
        )

        viewModel.discountModeType =
            arrayListOf(getString(R.string.percentage_symbol), getString(R.string.fixed_amount))
        binding.discountType.setAdapter(
            ArrayAdapter(
                this,
                R.layout.list_item,
                viewModel.discountType
            )
        )
        binding.discountMode.setAdapter(
            ArrayAdapter(
                this,
                R.layout.list_item,
                viewModel.discountModeType
            )
        )
        binding.recurringInvoice.setAdapter(
            ArrayAdapter(
                this,
                R.layout.list_item,
                viewModel.recurringInvoiceModes
            )
        )
        extensions.transformIntoDatePicker(
            binding.invoiceDate,
            this,
            null,
            null,
            "yyyy-MM-dd",
            true
        )
        extensions.transformIntoDatePicker(binding.dueDate, this, null, null, "yyyy-MM-dd")
    }

    private fun addItemView(selectedItemPosition: Int) {
        var item: Item? = null
        viewModel.initialInvoiceResponse.value?.item_data?.items?.forEachIndexed { index, proposalItem ->
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
            viewModel.initialInvoiceResponse.value?.item_data?.taxes,
            item,
            size,
            this
        )
        if (binding.dynamicItemsView.size > 0)
            binding.dynamicItemsView.addView(addProposalItemView.getSpaceView())
        binding.dynamicItemsView.addView(addProposalItemView.getView())
        hideKeyboard(currentFocus ?: View(this))
        extensions.showMessage(binding.root, getString(R.string.item_added))
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

    override fun onAddressUpdated(addressPickerData: AddressPickerData?) {
        viewModel.addressPickerData = addressPickerData
        binding.billTo.text =
            "${getString(R.string.bill_to)}\n\n ${addressPickerData?.streetBilling}\n ${addressPickerData?.cityBilling}, ${addressPickerData?.stateBilling}\n ${addressPickerData?.countryBilling?.short_name}, ${addressPickerData?.zipBilling}"
        if (addressPickerData?.allowShipping == true)
            binding.shipTo.text =
                "${getString(R.string.ship_to)}\n\n ${addressPickerData.streetShipping}\n ${addressPickerData.cityShipping}, ${addressPickerData.stateShipping}\n ${addressPickerData.countryShipping?.short_name}, ${addressPickerData.zipShipping}"
    }
}