package com.divesh.perfex.sales.presentation.proposals.manage

import android.content.SharedPreferences
import android.util.Log
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class ManageProposalsViewModel @Inject constructor(
    private val repository: SalesApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private var job: Job? = null

    private val originalRecords = MutableLiveData<List<ProposalsResponseModel.Proposal>>()
    private var tempRecords: List<ProposalsResponseModel.Proposal> = ArrayList()
    val currentRecords = MutableLiveData<List<ProposalsResponseModel.Proposal>>()
    val loadError = MutableLiveData<String?>()
    val filteredCount = MutableLiveData<Int?>()
    val totalCount = MutableLiveData<Int?>()
    val proposalDeletedAtPosition = MutableLiveData<Int>()
    val loading = MutableLiveData<Boolean>()

    fun refresh(relId: Int?, relType: String?) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getProposals(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), relId, relType
                )
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        withContext(Dispatchers.Main) {
                            originalRecords.value = response.body()?.proposals
                            currentRecords.value = response.body()?.proposals
                            totalCount.value = originalRecords.value?.size
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
                    Log.d(Constants.universalLogTag, e.stackTraceToString())
                    e.printStackTrace()
                    e.fillInStackTrace()
                    loadError.value = e.message.toString()
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
            for (list in originalRecords.value!!) {
                if (list.name?.isNotBlank() == true) {
                    if (list.name?.contains(searchString, true)!!) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.address?.isNotBlank() == true) {
                    if (list.address!!.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.invoice_id != null) {
                    if (list.invoice_id!!.contains(searchString, true)) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.subject?.isNotBlank() == true) {
                    if (list.subject?.contains(searchString, true)!!) {
                        tempRecords = tempRecords + list
                        continue
                    }
                }
                if (list.email?.isNotBlank() == true) {
                    if (list.email?.contains(searchString, true)!!) {
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

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }
}