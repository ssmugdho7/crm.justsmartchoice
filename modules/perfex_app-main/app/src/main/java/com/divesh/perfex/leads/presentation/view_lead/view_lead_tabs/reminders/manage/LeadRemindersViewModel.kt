package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.reminders.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.AssigneeList
import com.divesh.perfex.leads.domain.models.RemindersResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class LeadRemindersViewModel @Inject constructor(
    private val repository: LeadsApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private val startFrom = 0
    private val endTo = 100
    private var job: Job? = null

    val currentRecords = MutableLiveData<List<RemindersResponseModel.Reminder>>()
    val assigneeList = MutableLiveData<List<AssigneeList.Assignee>>()
    private val originalRecords = MutableLiveData<List<RemindersResponseModel.Reminder>>()
    val loadError = MutableLiveData<String?>()
    private val filteredCount = MutableLiveData<Int?>()
    private val totalCount = MutableLiveData<Int?>()
    val loading = MutableLiveData<Boolean>()
    val leadReminderCreated = MutableLiveData<Boolean>()
    val reminderDeletedAtposition = MutableLiveData<Int>()

    fun refresh(leadId : String) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.getLeadReminders(preferencesFile.getString(Constants.authenticationToken, "").toString() , startFrom, endTo, leadId , "lead" )
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        originalRecords.value = response.body()?.reminders
                        currentRecords.value = response.body()?.reminders
                        totalCount.value = originalRecords.value?.size
                        loading.value = false
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                }else{
                    loading.value = false
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun getAssignees() {
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.getAssignees(preferencesFile.getString(Constants.authenticationToken, "").toString())
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        loading.value = false
                        assigneeList.value = response.body()?.assignees
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                }else{
                    loading.value = false
                }
            }
        }
    }

    fun createReminder(sendMail: Int, description: String, date: String, relType: String, leadId: String) {
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.addReminder(preferencesFile.getString(Constants.authenticationToken, "").toString(), sendMail, description, date, relType, leadId)
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        loading.value = false
                        leadReminderCreated.value = true
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                } else {
                    loading.value = false
                }
            }
        }
    }

    fun deleteReminder(id: Int, position: Int) {
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.deleteReminder(preferencesFile.getString(Constants.authenticationToken, "").toString(), id)
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        loading.value = false
                        reminderDeletedAtposition.value = position
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                } else {
                    loading.value = false
                }
            }
        }
    }

    fun updateReminder(
        sendMail: Int,
        description: String,
        date: String,
        reminderId: Int
    ) {
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.editReminder(preferencesFile.getString(Constants.authenticationToken, "").toString(), sendMail, description, date, reminderId)
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        loading.value = false
                        leadReminderCreated.value = true
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                } else {
                    loading.value = false
                }
            }
        }
    }
}