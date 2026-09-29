package com.divesh.perfex.sales.presentation.invoices.presentation.manage

import android.content.SharedPreferences
import android.util.Log
import androidx.lifecycle.MutableLiveData
import com.google.gson.Gson
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.sales.domain.models.AddressPickerData
import com.divesh.perfex.sales.domain.models.invoices.InitialInvoiceResponseModel
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse
import com.divesh.perfex.sales.domain.models.proposals.SelectedItem
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import retrofit2.HttpException
import java.net.SocketTimeoutException
import javax.inject.Inject

@HiltViewModel
class ManageInvoicesViewModel @Inject constructor(
    private val repository: SalesApiRepository, private val preferencesFile: SharedPreferences
) : BaseViewModel() {
    val loadError = MutableLiveData<String?>()
    val initialInvoiceResponse = MutableLiveData<InitialInvoiceResponseModel?>()

    //initial data array to be set on views
    var currencies = arrayListOf<String>()
    var items = arrayListOf<String>()
    var customers = arrayListOf<String>()
    var staffList = arrayListOf<String>()
    var discountType = arrayListOf<String>()
    var discountModeType = arrayListOf<String>()
    var paymentModes: Array<String> = arrayOf()
    var recurringInvoiceModes = arrayListOf<String>()
    var addressPickerData: AddressPickerData? = null
    val addedItems = MutableLiveData<List<SelectedItem>>()

    var selectedPaymentModeIds = arrayListOf<String>()

    val subTotalAmount = MutableLiveData<Double>()
    val totalAmount = MutableLiveData<Double>()
    val totalTaxAmount = MutableLiveData<Double>()

    fun refresh(relId: Int?, relType: String?) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getInvoices(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    relId,
                    relType
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        responseBody.value = response.body()
                        responseMessage.value = response.body()?.message
                        if (response.body()?.status == 1) {
                            originalRecords.value = response.body()?.invoices
                            currentRecords.value = response.body()?.invoices
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                        } else {
                            loadError.value = "Invoices not found!"
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    Log.d(Constants.universalLogTag, e.stackTraceToString())
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    fun filterRecords(searchString: String) {
        if (originalRecords.value?.isEmpty() == true) {
            return
        }
        loading.value = true
        if (searchString.isNotEmpty()) {
            tempRecords = arrayListOf()
            val originalRecords: List<InvoiceResponse.Invoice> =
                originalRecords.value!! as List<InvoiceResponse.Invoice>
            for (list in originalRecords) {
                if (list.name.isNotBlank()) {
                    if (list.name.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.companyName?.isNotBlank() == true) {
                    if (list.companyName.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.invoiceNumber != null) {
                    if (list.invoiceNumber.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.fancyTotal?.isNotBlank() == true) {
                    if (list.fancyTotal.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
            }
            filteredCount.value = tempRecords.size
            currentRecords.value = tempRecords
        } else {
            filteredCount.value = 0
            currentRecords.value = originalRecords.value
        }
        loading.value = false
    }

    fun loadInitialInvoiceData() {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getInitialInvoiceData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        initialInvoiceResponse.value = response.body()
                        loading.value = false
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response?.errorBody().toString()
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    fun addOrUpdateItem(item: SelectedItem, index: Int, type: String) {
        try {
            if (type == "add") {
                if (addedItems.value == null) addedItems.value = listOf(item)
                else addedItems.value = addedItems.value!! + item
            } else if (type == "update") {
                val itemAdded = itemExistInAddedItems(item)
                if (!itemAdded) {
                    if (addedItems.value == null) addedItems.value = listOf(item)
                    else addedItems.value?.plusElement(item)
                } else {
                    addedItems.value?.forEach { addedItem ->
                        addedItem.takeIf { it.id == item.id }?.let {
                            addedItems.value!!.plusElement(item)
                            return@forEach
                        }
                    }
                }
            } else {
                val itemAdded = addedItems.value?.get(index)
                if (itemAdded != null) {
                    addedItems.value = addedItems.value?.toMutableList()?.apply {
                        removeAt(index)
                    }?.toList()
                }
            }
            updateInvoiceAmount()
        } catch (_: Exception) {

        }
    }

    private fun updateInvoiceAmount() {
        if (addedItems.value?.isNotEmpty() == true) {
            var subTotal = 0.00
            var totalTax = 0.00
            var taxRate: Double
            addedItems.value?.forEach {
                if (it.qty > 0) {
                    val itemSubTotal = (it.rate * it.qty)
                    taxRate = 0.00
                    it.taxes.forEach { tax ->
                        if (tax != null) {
                            taxRate += tax.taxrate
                        }
                    }
                    if (taxRate > 0) {
                        totalTax += (itemSubTotal * taxRate) / 100
                    }
                    subTotal += itemSubTotal
                }
            }
            subTotalAmount.value = subTotal
            totalTaxAmount.value = totalTax
            totalAmount.value = subTotal + totalTax
        } else {
            subTotalAmount.value = 0.00
            totalTaxAmount.value = 0.00
            totalAmount.value = 0.00
        }
    }

    private fun itemExistInAddedItems(item: SelectedItem): Boolean {
        var isExist = false
        if (addedItems.value != null) {
            if (addedItems.value?.isNotEmpty() == true) {
                addedItems.value?.forEach {
                    if (it.id == item.id) {
                        isExist = true
                        return@forEach
                    }
                }
            }
        }
        return isExist
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun addInvoice(
        cancel_merged_invoices: String,
        companyName: String,
        project_id: Int?,
        invoiceNumber: String?,
        invoiceDate: String,
        dueDate: String?,
        currency: String?,
        saleAgent: String?,
        recurringInvoice: String?,
        discountTypeSelected: String?,
        adminNote: String?,
        item: String?,
        subTotalAmount: Double?,
        discountMode: String?,
        discountAmount: Double?,
        discountAmountCalculated: Double?,
        adjustmentAmount: Double?,
        totalAmount: Double?,
        termsAndConditions: String?,
        clientNotes: String?,
        save_and_send: Boolean
    ) {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val clientId = getSelectedClientId(companyName)
                val currencyId = getSelectedCurrencyId(currency)
                val staffId = getSelectedSaleAgentId(saleAgent)
                val lastSelectedItem = addedItems.value?.last()
                val response = repository.addInvoice(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    cancel_merged_invoices,
                    clientId,
                    project_id,
                    if(addressPickerData != null ) addressPickerData?.streetBilling else "",
                    if(addressPickerData != null ) addressPickerData?.cityBilling else "",
                    if(addressPickerData != null ) addressPickerData?.stateBilling else "",
                    if(addressPickerData != null ) addressPickerData?.zipBilling else "",
                    if (addressPickerData?.showShippingDetailsOnInvoice == true) "on" else null,
                    if(addressPickerData != null )  addressPickerData?.streetShipping else "",
                    if(addressPickerData != null ) addressPickerData?.cityShipping else "",
                    if(addressPickerData != null ) addressPickerData?.stateShipping else "",
                    if(addressPickerData != null ) addressPickerData?.zipShipping else "",
                    invoiceNumber,
                    invoiceDate,
                    dueDate,
                    null,
                    selectedPaymentModeIds.toString(),
                    currencyId,
                    staffId,
                    recurringInvoiceModes.indexOf(recurringInvoice),
                    discountTypeSelected,
                    1,
                    "day",
                    adminNote,
                    item,
                    lastSelectedItem?.qty,
                    lastSelectedItem?.description,
                    lastSelectedItem?.long_description,
                    lastSelectedItem?.qty,
                    lastSelectedItem?.unit,
                    lastSelectedItem?.rate,
                    Gson().toJson(addedItems.value),
                    subTotalAmount,
                    if(discountMode == "%") discountAmount else 0.00,
                    discountAmountCalculated,
                    adjustmentAmount,
                    totalAmount,
                    null,
                    null,
                    clientNotes,
                    termsAndConditions,
                    save_and_send,
                )
                if (response.isSuccessful) {
                    withContext(Dispatchers.Main) {
                        responseBody.value = response.body()
                        responseMessage.value = response.body()?.message
                        loading.value = false
                        if(response.body()?.status == 1){
                            itemAdded.value = true
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    private fun getSelectedClientId(companyName: String): Int {
        val customers = initialInvoiceResponse.value?.customers
        var clientId = 0
        if(customers?.isNotEmpty() == true){
            customers.forEach {
                if(it?.company == companyName){
                    clientId = it.userid
                    return@forEach
                }
            }
        }
        return clientId
    }

    private fun getSelectedCurrencyId(currency: String?): Int {
        val currencies = initialInvoiceResponse.value?.currencies
        var currencyId = 0
        if(currencies?.isNotEmpty() == true){
            currencies.forEach {
                if(it?.name == currency){
                    if (it != null) {
                        currencyId = it.id
                    }
                    return@forEach
                }
            }
        }
        return currencyId
    }

    private fun getSelectedSaleAgentId(saleAgent: String?): Int {
        val staffList = initialInvoiceResponse.value?.staff_list
        var staffId = 0
        if(staffList?.isNotEmpty() == true){
            staffList.forEach {staff ->
                if("${staff?.firstname} ${staff?.lastname}" == saleAgent){
                    staffId = staff?.staffid!!
                    return@forEach
                }
            }
        }
        return staffId
    }
}
