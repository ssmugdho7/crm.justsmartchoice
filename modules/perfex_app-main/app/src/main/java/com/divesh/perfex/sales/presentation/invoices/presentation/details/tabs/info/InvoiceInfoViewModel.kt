package com.divesh.perfex.sales.presentation.invoices.presentation.details.tabs.info

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import java.net.SocketTimeoutException
import javax.inject.Inject

@HiltViewModel
class InvoiceInfoViewModel @Inject constructor(
    private val repository: SalesApiRepository,
    private val preferencesFile: SharedPreferences
) : BaseViewModel() {

    val staffId = MutableLiveData<Int>()

    init {
        CoroutineScope(Dispatchers.IO).launch {
            val staffIdTemp = preferencesFile.getInt(Constants.staffId, 0)
            withContext(Dispatchers.Main) {
                staffId.value = staffIdTemp
            }
        }
    }

    fun refresh(invoiceId: Int?) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.viewInvoice(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    invoiceId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = response.body()?.message
                        if (response.body()?.status == 1) {
                            responseBody.value = response.body()
                            originalRecords.value = response.body()?.data?.invoice?.items
                            currentRecords.value = response.body()?.data?.invoice?.items
                            totalCount.value = response.body()?.data?.invoice?.items?.size
                            loading.value = false
                        } else {
                            responseMessage.value = "Invoice not found!"
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
                    }
                }
            }  catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                e.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun markInvoiceAsSent(invoiceId: Int) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.markInvoiceAsSent(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    invoiceId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = response.body()?.message
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }
}