package com.divesh.perfex.tasks.presentation.add

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.RelatedTo
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import javax.inject.Inject

@HiltViewModel class AddTaskViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: LeadsApiRepository,
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val taskAdded = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val relatedToResponse = MutableLiveData<RelatedTo>()
    fun addTask(
        repeatEvery: String,
        priority: String,
        isBillable: Boolean,
        subject: String,
        hourlyRate: String,
        startDate: String,
        dueDate: String,
        repeatEveryCustomNumber: String,
        repeatEveryCustomType: String,
        totalCycles: String,
        relType: String?,
        relId: Int,
        description: String
    ) {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                var billable: String? = null
                if (isBillable) billable = "on"
                val response = repository.addTask(
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
                    description
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        taskAdded.value = true
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

    fun getRelatedToItems() {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getRelatedToItems(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        relatedToResponse.value = response.body()
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