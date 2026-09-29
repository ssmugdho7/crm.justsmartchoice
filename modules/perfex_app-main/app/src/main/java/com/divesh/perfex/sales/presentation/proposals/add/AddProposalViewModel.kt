package com.divesh.perfex.sales.presentation.proposals.add

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.sales.domain.models.proposals.InitialProposalResponseModel
import com.divesh.perfex.sales.domain.models.proposals.SelectedItem
import com.google.gson.Gson
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import retrofit2.HttpException
import java.net.SocketTimeoutException
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel
class AddProposalViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: SalesApiRepository
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val proposalAdded = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val internetProblem = MutableLiveData<Boolean>()
    val initialProposalResponse = MutableLiveData<InitialProposalResponseModel>()
    val addedItems = MutableLiveData<List<SelectedItem>>()
    val subTotalAmount = MutableLiveData<Double>()
    val totalAmount = MutableLiveData<Double>()
    val totalTaxAmount = MutableLiveData<Double>()

    init {
        getRelatedToItems()
    }

    private fun getRelatedToItems() {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getProposalInitialData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        initialProposalResponse.value = response.body()
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
                if (addedItems.value == null)
                    addedItems.value = listOf(item)
                else
                    addedItems.value = addedItems.value!! + item
            } else if (type == "update") {
                val itemAdded = itemExistInAddedItems(item)
                if (!itemAdded) {
                    if (addedItems.value == null)
                        addedItems.value = listOf(item)
                    else
                        addedItems.value?.plusElement(item)
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

    fun addProposal(
        subject: String,
        relType: String,
        relId: Int,
        projectId: String?,
        date: String,
        openTill: String?,
        currency: Int,
        discountType: String?,
        tags: String?,
        allowComments: String?,
        status: Int,
        assigned: Int,
        proposalTo: String?,
        address: String?,
        city: String,
        state: String?,
        country: Int,
        zip: String?,
        email: String,
        phone: String,
        itemSelected: String?,
        show_quantity_as: Int?,
        description: String?,
        longDescription: String?,
        quantity: Int,
        unit: String?,
        rate: Double?,
        taxName: String?,
        subTotal: Double,
        discountPercent: Double,
        discountTotal: Double?,
        adjustment: Double?,
        total: Double,
        saveAndSend: Boolean?
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
              /*val arrayListOfSelectedItemsForProposal = arrayListOf<SelectedItem>()
                addedItems.value?.forEach {
                    arrayListOfSelectedItemsForProposal += it
                }*/
                val response = repository.addProposal(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    subject,
                    relType,
                    relId,
                    projectId,
                    date,
                    openTill,
                    currency,
                    discountType,
                    tags,
                    allowComments,
                    status,
                    assigned,
                    proposalTo,
                    address,
                    city,
                    state,
                    country,
                    zip,
                    email,
                    phone,
                    itemSelected,
                    show_quantity_as,
                    description,
                    longDescription,
                    quantity,
                    unit,
                    rate,
                    taxName,
                    Gson().toJson(addedItems.value),
                    subTotal,
                    discountPercent,
                    discountTotal,
                    adjustment,
                    total,
                    saveAndSend
                )
                if (response.isSuccessful) {
                    withContext(Dispatchers.Main) {
                        if (response.body()?.status == 1) {
                            proposalAdded.value = true
                        }
                        loadError.value = response.body()?.message
                        loading.value = false
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        internetProblem.value = true
                    }
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    internetProblem.value = true
                }
            } catch (unknownHostException: UnknownHostException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "Internet connection is not stable!"
                }
            } catch (httpException: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (exception: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = exception.message.toString()
                }
            }
        }
    }
}