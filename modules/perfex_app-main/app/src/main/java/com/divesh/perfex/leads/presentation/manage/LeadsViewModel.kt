package com.divesh.perfex.leads.presentation.manage

import android.content.SharedPreferences
import android.util.Log
import androidx.lifecycle.MutableLiveData
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.helpers.dynamic_form.models.DynamicFormValues
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.*
import com.google.gson.Gson
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import retrofit2.HttpException
import java.net.SocketTimeoutException
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel
class LeadsViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: LeadsApiRepository,
) : BaseViewModel() {
    val statusDropdowns = MutableLiveData<List<LeadsStatus.Status?>>()
    val sourceDropdowns = MutableLiveData<List<LeadsStatus.Status?>>()
    val countryDropdowns = MutableLiveData<List<CountryListResponse.Country>?>()
    val assigneeDropdowns = MutableLiveData<List<AssigneeList.Assignee?>>()
    val lead = MutableLiveData<ViewLeadResponseModel.Lead?>()
    val customFields = MutableLiveData<List<CustomField?>>()
    val defaultSelectedFields = MutableLiveData<InitialLeadResponseModel.DefaultSelectedFields?>()
    var staffId = 0

    private var startFrom = 0
    private var limit = 1000

    fun loadInitialFormData() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getLeadInitialFormData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                staffId = preferencesFile.getInt(Constants.staffId, 0)
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            responseBody.value = response.body()
                            if(response.body()?.status == 1) {
                                defaultSelectedFields.value = response.body()?.defaultSelectedFields
                                statusDropdowns.value = response.body()?.leadStatus
                                sourceDropdowns.value = response.body()?.source
                                countryDropdowns.value = response.body()?.countries
                                assigneeDropdowns.value = response.body()?.assignees
                                customFields.value = response.body()?.customFields
                            }else{
                                responseMessage.value = "An error occurred"
                                loading.value = false
                            }
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            responseMessage.value = "An error occurred"
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
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
                    e.printStackTrace()
                    Log.d(Constants.universalLogTag, "MESSAGE OF ERROR : ${e.message} , ${e.cause}")
                    loading.value = false
                    responseMessage.value = e.message.toString()
                }
            }
        }
    }

    fun getLead(leadId : String) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getLead(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    leadId
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            responseMessage.value = response.body()?.message
                            if(response.body()?.status == 1) {
                                lead.value = response.body()?.lead
                            }else{
                                loading.value = false
                            }
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            responseMessage.value = "An error occurred"
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
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

    fun addLead(
        status: String,
        source: String,
        assigned: String,
        name: String,
        position: String,
        email: String,
        website: String,
        phone: String,
        company: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zipCode: String,
        description: String,
        isPublic: Boolean,
        lastContact: String,
        contactedToday: String?,
        leadValue: String,
        customFields: DynamicFormValues?
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.addLead(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    status,
                    source,
                    assigned,
                    name,
                    position,
                    email,
                    website,
                    phone,
                    company,
                    address,
                    city,
                    state,
                    country,
                    zipCode,
                    description,
                    isPublic,
                    lastContact,
                    contactedToday,
                    leadValue,
                    if(customFields != null) Gson().toJson(customFields) else null
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            if (response.body()?.status == 1)
                                itemAdded.value = true
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            responseMessage.value = "An error occurred"
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
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

    fun updateLead(
        status: String,
        source: String,
        assigned: String,
        name: String,
        position: String,
        email: String,
        website: String,
        phone: String,
        company: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zipCode: String,
        description: String,
        isPublic: Boolean,
        lastContact: String,
        leadValue: String,
        leadId: String,
        customFields: DynamicFormValues?
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateLead(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    status,
                    source,
                    assigned,
                    name,
                    position,
                    email,
                    website,
                    phone,
                    company,
                    address,
                    city,
                    state,
                    country,
                    zipCode,
                    description,
                    isPublic,
                    lastContact,
                    leadValue,
                    leadId,
                    if(customFields != null) Gson().toJson(customFields) else null
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            responseMessage.value  = response.body()?.message
                            if(response.body()?.status == 1)
                                itemAdded.value = true
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            responseMessage.value  = "An error occurred"
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value  = "An error occurred"
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
                    loading.value = false
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun deleteLead(leadId: Int, position: Int) {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.deleteLead(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), leadId
                )
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        withContext(Dispatchers.Main) {
                            itemDeletedAtPosition.value = position
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            responseMessage.value = response.body()?.message
                            loading.value = false
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        responseMessage.value = "An error occurred"
                        loading.value = false
                    }
                }
            } catch (unknownHostException: UnknownHostException) {
                unknownHostException.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = "Internet connection is not stable!"
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }

    fun loadLeads() {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getLeads(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), startFrom, limit
                )
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        withContext(Dispatchers.Main) {
                            originalRecords.value = response.body()?.leads
                            currentRecords.value = response.body()?.leads
                            totalCount.value = originalRecords.value?.size
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            responseMessage.value = response.body()?.message
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        responseMessage.value = "An error occurred"
                    }
                }
            } catch (unknownHostException: UnknownHostException) {
                unknownHostException.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = "Internet connection is not stable!"
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
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
            val leads = originalRecords.value!! as List<Lead>
            for (list in leads) {
                if (list.name?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.title?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.source?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.status?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.phonenumber?.contains(searchString, true) == true) {
                    tempRecords = tempRecords + list
                    continue
                }
                if (list.email?.isNotBlank() == true) {
                    if (list.email.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
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
}