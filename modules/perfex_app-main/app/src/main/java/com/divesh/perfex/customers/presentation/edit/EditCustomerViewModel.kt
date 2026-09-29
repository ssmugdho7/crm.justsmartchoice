package com.divesh.perfex.customers.presentation.edit

import android.content.SharedPreferences
import android.util.Log
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel class EditCustomerViewModel  @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: CustomersApiRepository,
): ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val customer = MutableLiveData<CustomerResponseModel>()
    val customerUpdated = MutableLiveData<Boolean>()

    fun updateCustomer(
        company: String,
        vat: String,
        phonenumber: String?,
        website: String?,
        groups_in: String?,
        address: String?,
        city: String?,
        state: String?,
        zip: String?,
        country: Int,
        billing_street: String?,
        billing_city: String?,
        billing_state: String?,
        billing_zip: String?,
        billing_country: Int,
        shipping_street: String?,
        shipping_city: String?,
        shipping_state: String?,
        shipping_zip: String?,
        shipping_country: Int
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.updateCustomer(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    company,
                    vat,
                    phonenumber,
                    website,
                    groups_in,
                    address,
                    city,
                    state,
                    zip,
                    country,
                    billing_street,
                    billing_city,
                    billing_state,
                    billing_zip,
                    billing_country,
                    shipping_street,
                    shipping_city,
                    shipping_state,
                    shipping_zip,
                    shipping_country,
                    customer.value?.data?.client?.userid
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loadError.value = response.body()?.message
                            loading.value = false
                            if(response.body()?.status == 1){
                                customerUpdated.value = true
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
                    e.printStackTrace()
                    Log.e(Constants.universalLogTag, e.message.toString())
                    loadError.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }

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
                e.printStackTrace()
                Log.e(Constants.universalLogTag, e.message.toString())
                withContext(Dispatchers.Main) {
                    loadError.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }
}