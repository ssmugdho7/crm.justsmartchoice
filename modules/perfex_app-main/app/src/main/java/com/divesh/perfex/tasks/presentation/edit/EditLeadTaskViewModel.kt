package com.divesh.perfex.tasks.presentation.edit

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import javax.inject.Inject

@HiltViewModel class EditLeadTaskViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: LeadsApiRepository,
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val taskUpdated = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    fun updateTask(
        repeatEvery: String,
        priority: String,
        billable: String?,
        subject: String,
        hourlyRate: String,
        startDate: String,
        dueDate: String,
        repeatEveryCustomNumber: String,
        repeatEveryCustomType: String,
        totalCycles: String,
        relType: String,
        relId: String,
        description: String,
        taskId: Int
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateTask(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    billable,
                    subject,
                    hourlyRate,
                    "0",
                    startDate,
                    dueDate,
                    priority,
                    repeatEvery,
                    repeatEveryCustomNumber,
                    repeatEveryCustomType,
                    totalCycles,
                    relType,
                    relId,
                    "",
                    description,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        if(response.body()?.status == 1)
                            taskUpdated.value = true
                        loadError.value = response.body()?.message
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
                    loadError.value = e.message.toString()
                }
            } catch (e: Throwable) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }
}