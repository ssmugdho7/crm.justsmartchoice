package com.divesh.perfex.tasks.presentation.manage

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.Task
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import java.net.UnknownHostException
import javax.inject.Inject
import kotlin.Exception

@HiltViewModel
class TaskViewModel @Inject constructor(
    private val repository: LeadsApiRepository, private val preferencesFile: SharedPreferences
) : ViewModel() {
    private var startFrom = 0
    private var limit = 1000

    val currentRecords = MutableLiveData<List<Task>>()
    private val originalRecords = MutableLiveData<List<Task>>()
    private var tempRecords: List<Task> = ArrayList()
    val loadError = MutableLiveData<String?>()
    val filteredCount = MutableLiveData<Int>()
    val totalCount = MutableLiveData<Int>()
    val removedTaskAtPosition = MutableLiveData<Int>()
    val loading = MutableLiveData<Boolean>()
    val taskTimerUpdated = MutableLiveData<Boolean>()

    fun filterRecords(searchString: String) {
        if (originalRecords.value?.isEmpty() == true) {
            return
        }
        loading.value = true
        if (searchString.isNotEmpty()) {
            tempRecords = arrayListOf()
            for (list in originalRecords.value!!) {
                if (list.assignees != null) {
                    if (list.assignees.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.task_name?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.hourly_rate.toString().contains(searchString, true)) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.total_cycles.toString().contains(searchString, true)) {
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

    fun refresh(relId: Int, relType: String) {
        filteredCount.value = 0
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getTasks(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), startFrom, limit, relId, relType
                )
                if (response != null) {
                    if (response.isSuccessful) {
                        withContext(Dispatchers.Main) {
                            originalRecords.value = response.body()?.tasks
                            currentRecords.value = response.body()?.tasks
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                            if(response.body()?.status == 0)
                                loadError.value = response.body()?.message
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

    fun deleteTask(taskId: Int, position: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.deleteTask(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), taskId
                )
                if (response?.isSuccessful == true) {
                    response.body()?.let {
                        withContext(Dispatchers.Main) {
                            loadError.value = it.message
                            if (it.status == 1) {
                                removedTaskAtPosition.value = position
                            }
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            }
        }
    }

    fun updateTaskStatus(taskId: Int, status: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateTaskStatus(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), taskId, status
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        loadError.value = response.body()?.message
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            }
        }
    }

    fun startOrStopTask(taskId: Int, notFinishedTimerByCurrentStaff: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.startOrStopTask(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    taskId,
                    notFinishedTimerByCurrentStaff
                )
                if (response?.isSuccessful == true) {
                    response.body()?.let {
                        withContext(Dispatchers.Main) {
                            loadError.value = it.message
                            if (it.status == 1)
                                taskTimerUpdated.value = true
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loadError.value = "An error occurred"
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loadError.value = e.message
                }
            }
        }
    }
}