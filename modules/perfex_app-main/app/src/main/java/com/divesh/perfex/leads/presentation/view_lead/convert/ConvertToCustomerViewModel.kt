package com.divesh.perfex.leads.presentation.view_lead.convert

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.leads.data.repositories.LeadsApiRepository
import com.divesh.perfex.leads.domain.models.CountryListResponse
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import javax.inject.Inject

@HiltViewModel
class ConvertToCustomerViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: LeadsApiRepository,
): ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val converted = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val countryId = MutableLiveData<Int>()
    val countryListResponse = MutableLiveData<CountryListResponse>()

    fun convertToCustomer(
        leadId: Int,
        firstName: String,
        lastName: String,
        position: String,
        email: String,
        company: String,
        phone: String,
        website: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zipCode: String,
        password: String,
        sendSetPasswordEmail: String,
        doNotSendWelcomeEmail: String,
        originalLeadEmail: String
    ) {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.convertToCustomer(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    leadId,
                    firstName,
                    lastName,
                    position,
                    email,
                    company,
                    phone,
                    website,
                    address,
                    city,
                    state,
                    country,
                    zipCode,
                    password,
                    sendSetPasswordEmail,
                    doNotSendWelcomeEmail,
                    originalLeadEmail
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        loadError.value = response.body()?.message
                        loading.value = false
                        if(response.body()?.status == 1)
                            converted.value = true
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response?.errorBody().toString()
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            } catch (e: Throwable) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    fun initCountryDropdown(country: Int){
        loading.value = true
        countryId.value = country
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getCountryList(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        countryListResponse.value = response.body()
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = response?.errorBody().toString()
                    }
                }
            } catch (e: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            } catch (e: Throwable) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }
}