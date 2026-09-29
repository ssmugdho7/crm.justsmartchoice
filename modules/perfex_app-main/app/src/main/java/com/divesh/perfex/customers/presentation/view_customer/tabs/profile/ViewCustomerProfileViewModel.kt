package com.divesh.perfex.customers.presentation.view_customer.tabs.profile

import android.content.SharedPreferences
import android.util.Log
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
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
class ViewCustomerProfileViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: CustomersApiRepository,
    private val leadRepository: LeadsApiRepository,
): ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val customer = MutableLiveData<CustomerResponseModel>()
    val countryListResponse = MutableLiveData<CountryListResponse>()

    fun getCustomer(customerId: Int) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getCustomer(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    "profile",
                    customerId
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            customer.value = response.body()
                            loading.value = false
                            loadError.value = response.body()?.message
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
                    Log.e(Constants.universalLogTag, e.message.toString())
                    e.printStackTrace()
                    loadError.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }

    fun initCountryDropdown(){
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = leadRepository.getCountryList(
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