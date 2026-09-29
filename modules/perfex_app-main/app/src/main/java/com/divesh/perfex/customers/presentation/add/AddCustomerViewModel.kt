package com.divesh.perfex.customers.presentation.add

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import com.divesh.perfex.customers.domain.models.CustomerAddedResponseModel
import com.divesh.perfex.customers.domain.models.CustomersAddFormResponse
import com.divesh.perfex.leads.domain.models.CountryListResponse
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel
class AddCustomerViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val repository: CustomersApiRepository,
) : ViewModel() {
    val loading = MutableLiveData<Boolean>()
    val createContact = MutableLiveData<Boolean>()
    val loadError = MutableLiveData<String?>()
    val groupsDropdowns = MutableLiveData<List<CustomersAddFormResponse.CustomersGroups>?>()
    val countryDropdowns = MutableLiveData<List<CountryListResponse.Country>?>()
    val customerAddedResponse = MutableLiveData<CustomerAddedResponseModel>()
    val defaultCountry = MutableLiveData<Int?>()

    init {
        loadInitialFormData()
    }

    private fun loadInitialFormData() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.getCustomerInitialFormData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            defaultCountry.value = response.body()?.defaultCountry
                            groupsDropdowns.value = response.body()?.groups
                            countryDropdowns.value = response.body()?.countries
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

    fun addCustomer(
        createContactAfterSaving: Boolean,
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
                val response = repository.addCustomer(
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
                    shipping_country
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            customerAddedResponse.value = response.body()
                            createContact.value = createContactAfterSaving
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
                    loadError.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }
}