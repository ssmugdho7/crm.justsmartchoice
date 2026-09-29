package com.divesh.perfex.tasks.presentation.view.tabs

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import retrofit2.HttpException
import java.net.SocketTimeoutException
import javax.inject.Inject

@HiltViewModel
class ViewTaskViewModel @Inject constructor(
    private val repository: SalesApiRepository,
    private val preferencesFile: SharedPreferences
) : BaseViewModel() {
    val commentAdded = MutableLiveData<Boolean>()
    val attachmentAdded = MutableLiveData<Boolean>()
    val checklistItemAdded = MutableLiveData<Boolean>()
    val viewTaskResponse = MutableLiveData<ViewTaskResponseModel?>()

    fun loadViewTaskInitialData(taskId: Int) {
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.viewTask(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        viewTaskResponse.value = response.body()
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun updateTaskAssignees(assigneeIds: String, taskId: String) {
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateTaskAssignees(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    assigneeIds,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun updateTaskFollowers(followerIds: String, taskId: String) {
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateTaskFollowers(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    followerIds,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun removeAttachment(attachmentId: Int, taskId: Int){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.removeAttachment(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    attachmentId
                )
                if (response?.isSuccessful == true) {
                    loadViewTaskInitialData(taskId)
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun removeComment(commentId: Int, taskId: Int){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.removeComment(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    commentId
                )
                if (response?.isSuccessful == true) {
                    loadViewTaskInitialData(taskId)
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun addAttachment(taskId: RequestBody, attachment: MultipartBody.Part?) {
        loading.value = true
        val staffId: RequestBody = preferencesFile.getInt(Constants.staffId, 0).toString().toRequestBody("multipart/form-data".toMediaTypeOrNull())
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.addAttachment(
                    staffId,
                    attachment,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        loading.value = false
                        if(response.body()?.status == 1){
                            attachmentAdded.value = true
                        }
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun updateComment(comment: String, commentId: String) {
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateComment(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    comment,
                    commentId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                        commentAdded.value = true
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun addComment(taskId: RequestBody, comment: RequestBody, attachment: MultipartBody.Part?) {
        loading.value = true
        val staffId: RequestBody = preferencesFile.getInt(Constants.staffId, 0).toString().toRequestBody("multipart/form-data".toMediaTypeOrNull())
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.addComment(
                    staffId,
                    taskId,
                    comment,
                    attachment
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        loading.value = false
                        if(response.body()?.status == 1){
                            commentAdded.value = true
                        }
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun removeChecklistItem(checklistId: Int, taskId: Int){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.removeChecklistItem(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    checklistId
                )
                if (response?.isSuccessful == true) {
                    loadViewTaskInitialData(taskId)
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun onChecklistItemToggled(checklistId: Int, isChecked: Boolean){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.onChecklistItemToggled(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    checklistId,
                    if(isChecked) 1 else 0
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun saveAsTemplate(description: String){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.saveTemplate(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    description
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun addCheckListItem(description: String, taskId: String){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.addCheckListItem(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    description,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        checklistItemAdded.value = true
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun updateCheckListItem(description: String, checklistId: Int){
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateCheckListItem(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    description,
                    checklistId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun assignStaffToChecklist(staffId: Int, checklistId: Int, taskId: Int) {
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.assignStaffToChecklist(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    staffId,
                    checklistId,
                    taskId
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main){
                        responseMessage.value = response.body()?.message
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }
}