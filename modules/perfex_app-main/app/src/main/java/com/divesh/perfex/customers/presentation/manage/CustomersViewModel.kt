package com.divesh.perfex.customers.presentation.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.customers.domain.models.CustomerListResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel
class CustomersViewModel @Inject constructor(
    private val repository: CustomersApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private var startFrom = 0
    private var limit = 10000

    val currentRecords = MutableLiveData<List<CustomerListResponseModel.Customer>>()
    private val originalRecords = MutableLiveData<List<CustomerListResponseModel.Customer>>()
    private var tempRecords: List<CustomerListResponseModel.Customer> = ArrayList()
    val loadError = MutableLiveData<String?>()
    val filteredCount = MutableLiveData<Int>()
    val totalCount = MutableLiveData<Int>()
    val removedCustomerAtPosition = MutableLiveData<Int>()
    val loading = MutableLiveData<Boolean>()

    fun filterRecords(searchString: String) {
        if (originalRecords.value?.isEmpty() == true) {
            return
        }
        loading.value = true
        if (searchString.isNotEmpty()) {
            tempRecords = arrayListOf()
            for (list in originalRecords.value!!) {
                if (list.firstname?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.company?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.lastname?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.lastname.toString().contains(searchString, true)) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.phonenumber.toString().contains(searchString, true)) {
                    tempRecords = tempRecords + list
                    continue
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

    fun refresh() {
        filteredCount.value = 0
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getCustomerList(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), startFrom, limit
                )
                if (response != null) {
                    if (response.isSuccessful) {
                        withContext(Dispatchers.Main) {
                            loadError.value = null
                            originalRecords.value = response.body()?.customers
                            currentRecords.value = response.body()?.customers
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            loadError.value = response.errorBody().toString()
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = "An error occurred"
                    }
                }
            } catch (unknownHostException: UnknownHostException) {
                unknownHostException.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "Internet connection is not stable!"
                }
            } catch (e: Exception) {
                e.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    fun deleteCustomer(customerId: Int, position: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.deleteCustomer(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    customerId
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loadError.value = response.body()?.message
                            loading.value = false
                            if (response.body()?.status == 1) {
                                removedCustomerAtPosition.value = position
                            }
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                        loading.value = false
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                    loading.value = false
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                    loading.value = false
                }
            }
        }
    }

    fun updateActiveStatus(checked: Boolean, customerId: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val isActive = if (checked) 1 else 0
                val response = repository.updateActiveStatus(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    isActive,
                    customerId
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loadError.value = response.body()?.message
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                        loading.value = false
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                    loading.value = false
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                    loading.value = false
                }
            }
        }
    }
}