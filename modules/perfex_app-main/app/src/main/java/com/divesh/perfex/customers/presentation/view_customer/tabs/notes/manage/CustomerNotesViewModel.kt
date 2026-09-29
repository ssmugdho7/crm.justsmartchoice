package com.divesh.perfex.customers.presentation.view_customer.tabs.notes.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.customers.domain.models.CustomerNotesResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import javax.inject.Inject

@HiltViewModel
class CustomerNotesViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: CustomersApiRepository,
): ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val noteDeletedAtPosition = MutableLiveData<Int?>()

    private val originalRecords = MutableLiveData<List<CustomerNotesResponseModel.Note>>()
    private var tempRecords: List<CustomerNotesResponseModel.Note> = ArrayList()
    val currentRecords = MutableLiveData<List<CustomerNotesResponseModel.Note>>()
    val filteredCount = MutableLiveData<Int?>()
    val totalCount = MutableLiveData<Int?>()

    fun getCustomerNotes(customerId: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getNotes(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    customerId,
                    "customer"
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            originalRecords.value = response.body()?.notes
                            currentRecords.value = response.body()?.notes
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            loadError.value = "An error occurred"
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                        loading.value = false
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message.toString()
                    loading.value = false
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
            for (list in originalRecords.value!!) {
                if (list.description?.contains(searchString, true) == true) {
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

    fun deleteCustomerNote(noteId: Int, position: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.deleteNote(preferencesFile.getString(Constants.authenticationToken, "").toString(), noteId)
                if (response?.isSuccessful == true) {
                    if(response.body() != null){
                        withContext(Dispatchers.Main) {
                            loadError.value = response.body()?.message
                            loading.value = false
                            if (response.body()?.status == 1) {
                                noteDeletedAtPosition.value = position
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

    fun updateContactActiveStatus(checked: Boolean, contactId: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val isActive = if(checked) 1 else 0
                val response = repository.updateContactActiveStatus(preferencesFile.getString(Constants.authenticationToken, "").toString(), isActive, contactId)
                if (response?.isSuccessful == true) {
                    if(response.body() != null){
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