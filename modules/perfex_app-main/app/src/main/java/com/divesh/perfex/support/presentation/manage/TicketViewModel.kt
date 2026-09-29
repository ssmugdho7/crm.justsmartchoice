package com.divesh.perfex.support.presentation.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.support.data.repositories.SupportApiRepository
import com.divesh.perfex.support.domain.models.SupportResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel
class TicketViewModel @Inject constructor(
    private val repository: SupportApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private val startFrom = 0
    private val endTo = 100
    private var job: Job? = null

    val currentRecords = MutableLiveData<List<SupportResponseModel.Ticket>>()
    private val originalRecords = MutableLiveData<List<SupportResponseModel.Ticket>>()
    val loadError = MutableLiveData<String?>()
    val filteredCount = MutableLiveData<Int?>()
    val totalCount = MutableLiveData<Int?>()
    val loading = MutableLiveData<Boolean>()
    val ticketDeletedPosition = MutableLiveData<Int>()
    private var tempRecords: List<SupportResponseModel.Ticket> = ArrayList()

    fun refresh() {
        fetchRecords()
    }

    private fun fetchRecords() {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getTickets(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    startFrom,
                    endTo
                )
                withContext(Dispatchers.Main) {
                    if (response != null) {
                        if (response.isSuccessful) {
                            loading.value = false
                            if(response.body()?.status == 1) {
                                originalRecords.value = response.body()?.tickets
                                currentRecords.value = response.body()?.tickets
                                totalCount.value = originalRecords.value?.size
                            }else{
                                loadError.value = response.body()?.message
                            }
                        } else {
                            loading.value = false
                            loadError.value = response.errorBody().toString()
                        }
                    } else {
                        loading.value = false
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

    fun filterRecords(searchString: String) {
        if (originalRecords.value?.isEmpty() == true) {
            return
        }
        loading.value = true
        if (searchString.isNotEmpty()) {
            tempRecords = arrayListOf()
            for (list in originalRecords.value!!) {
                if (list.email?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.lastreply?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.message?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.name?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.subject.contains(searchString, true)) {
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

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun deleteTicket(id: Int, position: Int) {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            val response = repository.deleteTicket(preferencesFile.getString(Constants.authenticationToken, "").toString(), id)
            if (response != null) {
                if (response.isSuccessful) {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response.body()?.message
                        ticketDeletedPosition.value = position
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = "An error occurred"
                    }
                }
            } else {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "An error occurred"
                }
            }
        }
    }
}