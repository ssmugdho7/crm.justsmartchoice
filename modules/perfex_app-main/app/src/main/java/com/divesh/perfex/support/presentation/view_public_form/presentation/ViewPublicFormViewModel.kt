package com.divesh.perfex.support.presentation.view_public_form.presentation

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.support.data.repositories.SupportApiRepository
import com.divesh.perfex.support.domain.models.TicketRepliesResponseModel
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
class ViewPublicFormViewModel @Inject constructor(
    private val repository: SupportApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    val currentRecords = MutableLiveData<List<TicketRepliesResponseModel.Reply>>()
    val loading = MutableLiveData<Boolean>()
    val replyAdded = MutableLiveData<Boolean>()
    val internetProblem = MutableLiveData<Boolean>()
    val responseMessage = MutableLiveData<String?>()
    private var job: Job? = null

    fun refresh(ticketId: Int) {
        fetchRecords(ticketId)
    }

    private fun fetchRecords(ticketId: Int) {
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.getTicketReplies(
                preferencesFile.getString(Constants.authenticationToken, "").toString(),
                ticketId
            )
            withContext(Dispatchers.Main) {
                if (response != null) {
                    if (response.isSuccessful) {
                        currentRecords.value = response.body()?.replies
                        loading.value = false
                    } else {
                        loading.value = false
                    }
                } else {
                    loading.value = false
                }
            }
        }
    }

    fun addTicketReply(ticketId: RequestBody, message: RequestBody, image: MultipartBody.Part?){
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val staffId: RequestBody = preferencesFile.getInt(Constants.staffId, 0).toString()
                    .toRequestBody("multipart/form-data".toMediaTypeOrNull())
                val response = repository.addTicketReply(
                    staffId,
                    ticketId,
                    message,
                    image
                )
                withContext(Dispatchers.Main) {
                    if (response != null) {
                        if (response.isSuccessful) {
                            responseMessage.value = response.body()?.message
                            loading.value = false
                            if (response.body()?.status == 1) {
                                replyAdded.value = true
                            }
                        } else {
                            loading.value = false
                        }
                    } else {
                        loading.value = false
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

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }
}