package com.divesh.perfex.customers.presentation.view_customer.tabs.notes.edit

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel
class EditNoteViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val apiRepository: CustomersApiRepository,
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val noteUpdated = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()

    fun updateNote(
        noteId: Int,
        description: String
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = apiRepository.updateNote(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    noteId,
                    description
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            if(response.body()?.status == 1){
                                noteUpdated .value = true
                            }
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