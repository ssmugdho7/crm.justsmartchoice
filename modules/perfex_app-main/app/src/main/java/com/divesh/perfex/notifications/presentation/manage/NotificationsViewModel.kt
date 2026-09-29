package com.divesh.perfex.notifications.presentation.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.notifications.data.repositories.NotificationsApiRepository
import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class NotificationsViewModel @Inject constructor(
    private val repository: NotificationsApiRepository,
    private val preferencesFile: SharedPreferences
): ViewModel() {
    private var startFrom = 0
    private var limit = 1000
    private var job: Job? = null

    val currentRecords = MutableLiveData<List<NotificationsResponseModel.Notification>>()
    val loadError = MutableLiveData<String?>()
    val loading = MutableLiveData<Boolean?>()
    val notificationMarkedAsRead = MutableLiveData<Boolean>()
    val filteredCount = MutableLiveData<Int?>()

    fun refresh() {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getNotifications(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), startFrom, limit
                )
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        withContext(Dispatchers.Main) {
                            currentRecords.value = response.body()?.notifications
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            loadError.value = response.body()?.message
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    fun markNotificationAsRead(notificationId: Int) {
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.markNotificationAsRead(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), notificationId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response.body()?.message
                        if(response.body()?.status == 1){
                            notificationMarkedAsRead.value = true
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }
}