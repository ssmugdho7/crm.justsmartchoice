package com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.notes

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.LeadNotes
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class LeadNotesViewModel @Inject constructor(
    private val repository: LeadsApiRepository,
    private val preferencesFile: SharedPreferences
) : ViewModel() {
    private val startFrom = 0
    private val endTo = 100
    private var job: Job? = null

    val currentRecords = MutableLiveData<List<LeadNotes.Note>>()
    private val originalRecords = MutableLiveData<List<LeadNotes.Note>>()
    val loadError = MutableLiveData<String?>()
    private val filteredCount = MutableLiveData<Int?>()
    private val totalCount = MutableLiveData<Int?>()
    val loading = MutableLiveData<Boolean>()
    val noteDeletedPosition = MutableLiveData<Int>()

    fun refresh(leadId : String) {
        fetchRecords(leadId)
    }

    private fun fetchRecords( leadId : String) {
        filteredCount.value = 0
        loading.value = true
        job = CoroutineScope(Dispatchers.IO).launch {
            val response = repository.getLeadNotes(preferencesFile.getString(Constants.authenticationToken, "").toString(), startFrom, endTo, leadId , "lead" )
            withContext(Dispatchers.Main) {
                if(response != null) {
                    if (response.isSuccessful) {
                        loadError.value = null
                        originalRecords.value = response.body()?.notes
                        currentRecords.value = response.body()?.notes
                        totalCount.value = originalRecords.value?.size
                        loading.value = false
                    } else {
                        loading.value = false
                        loadError.value = response.errorBody().toString()
                    }
                }else{
                    loading.value = false
                }
            }
        }
    }

    override fun onCleared() {
        super.onCleared()
        job?.cancel()
    }

    fun addNotes(description: String, date: String, relType: String, leadId: String) {
        CoroutineScope(Dispatchers.IO).launch {
            val response = repository.addNote(
                preferencesFile.getString(Constants.authenticationToken, "").toString(),
                description,
                date,
                relType,
                leadId
            )
            if(response != null){
                if(response.isSuccessful){
                    withContext(Dispatchers.Main){
                        loadError.value = response.body()?.message
                    }
                }else{
                    withContext(Dispatchers.Main){
                        loadError.value = "An error occurred"
                    }
                }
            }else{
                withContext(Dispatchers.Main){
                    loadError.value = "An error occurred"
                }
            }
        }
    }

    fun deleteNotes(id: Int, position: Int){
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            val response = repository.deleteNotes(preferencesFile.getString(Constants.authenticationToken, "").toString(), id)
            if(response != null){
                if(response.isSuccessful){
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response.body()?.message
                        noteDeletedPosition.value = position
                    }
                }else{
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = "An error occurred"
                    }
                }
            }else{
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "An error occurred"
                }
            }
        }
    }

    fun updateLeadNote(description: String, noteId: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            val response = repository.editNote(preferencesFile.getString(Constants.authenticationToken, "").toString(), description, noteId)
            if(response != null){
                if(response.isSuccessful){
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response.body()?.message
                    }
                }else{
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = "An error occurred"
                    }
                }
            }else{
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "An error occurred"
                }
            }
        }
    }
}