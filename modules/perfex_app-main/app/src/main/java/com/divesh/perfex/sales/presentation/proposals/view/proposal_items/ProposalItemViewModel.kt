package com.divesh.perfex.sales.presentation.proposals.view.proposal_items

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.sales.data.repositories.SalesApiRepository
import com.divesh.perfex.sales.domain.models.proposals.ProposalItemsResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class ProposalItemViewModel  @Inject constructor(
    private val repository: SalesApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private var job: Job? = null
    val currentRecords = MutableLiveData<ProposalItemsResponseModel>()
    val loadError = MutableLiveData<String?>()
    val filteredCount = MutableLiveData<Int?>()
    val totalCount = MutableLiveData<Int?>()
    val loading = MutableLiveData<Boolean>()

    fun refresh(proposalId: Int) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getRelItems(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(), proposalId, "proposal"
                )
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        withContext(Dispatchers.Main) {
                            currentRecords.value = response.body()
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

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }
}