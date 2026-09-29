package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.activity_log

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.LeadActivityLogs
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import java.lang.Exception
import javax.inject.Inject

@HiltViewModel
class LeadsActivityLogsViewModel @Inject constructor(
    private val repository: LeadsApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private var job: Job? = null
    val currentRecords = MutableLiveData<List<LeadActivityLogs.Log>>()
    private val originalRecords = MutableLiveData<List<LeadActivityLogs.Log>>()
    val loadError = MutableLiveData<String?>()
    private val filteredCount = MutableLiveData<Int?>()
    private val totalCount = MutableLiveData<Int?>()
    val loading = MutableLiveData<Boolean>()
    val logAdded = MutableLiveData<Boolean>()
    fun refresh(leadId: String) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getLeadActivityLog(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    leadId.toInt()
                )
                withContext(Dispatchers.Main) {
                    if (response != null) {
                        if (response.isSuccessful) {
                            loadError.value = null
                            originalRecords.value = response.body()?.logs
                            currentRecords.value = response.body()?.logs
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                        } else {
                            loading.value = false
                            loadError.value = response.errorBody().toString()
                        }
                    } else {
                        loading.value = false
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun addActivityLog(description: String, leadId: String) {
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.addLeadActivityLog(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    leadId.toInt(),
                    description
                )
                withContext(Dispatchers.Main) {
                    if (response != null) {
                        if (response.isSuccessful) {
                            loadError.value = null
                            loading.value = false
                            logAdded.value = true
                        } else {
                            loading.value = false
                            loadError.value = response.errorBody().toString()
                        }
                    } else {
                        loading.value = false
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }
}