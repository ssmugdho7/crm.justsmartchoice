package com.divesh.perfex.support.presentation.edit

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.support.data.repositories.SupportApiRepository
import com.divesh.perfex.support.domain.models.InitialTicketResponseModel
import com.divesh.perfex.support.domain.models.SupportResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel
class EditTicketViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: SupportApiRepository,
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val leadAdded = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val initialTicketResponseModel = MutableLiveData<InitialTicketResponseModel>()
    val ticket = MutableLiveData<SupportResponseModel.Ticket>()
    init {
        loadInitialFormData()
    }

    private fun loadInitialFormData() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getInitialData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            initialTicketResponseModel.value = response.body()
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
                e.printStackTrace()
                withContext(Dispatchers.Main) {
                    loadError.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }

    fun updateTicket(
        ticketId: Int,
        subject: String,
        contactId: Int,
        name: String?,
        email: String?,
        departmentId: Int,
        cc: String?,
        tags: String?,
        assigned: Int?,
        priority: Int,
        service: Int?,
        project_id: Int?,
        message: String
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateTicket(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    ticketId, subject, contactId, name, email, departmentId, cc, tags, assigned, priority, service, project_id, message
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            leadAdded.value = true
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
}