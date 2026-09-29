package com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.edit

import android.content.SharedPreferences
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel
class EditContactViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val apiRepository: CustomersApiRepository,
) : BaseViewModel() {
    fun editContact(
        userId: Int?,
        firstName: String,
        lastName: String,
        title: String?,
        email: String,
        phoneNumber: String?,
        direction: String?,
        fakeUserNameRemembered: String?,
        fakePasswordRemembered: String?,
        password: String?,
        isPrimary: String?,
        permissions: ArrayList<Int>?,
        invoiceEmails: String?,
        estimateEmails: String?,
        creditNoteEmails: String?,
        projectEmails: String?,
        ticketEmails: String?,
        taskEmails: String?,
        contractEmails: String?
    ) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = apiRepository.editCustomerContact(
                    preferencesFile.getString(Constants.authenticationToken, "").toString(),
                    userId,
                    firstName,
                    lastName,
                    title,
                    email,
                    phoneNumber,
                    direction,
                    fakeUserNameRemembered,
                    fakePasswordRemembered,
                    password,
                    isPrimary,
                    permissions,
                    invoiceEmails,
                    estimateEmails,
                    creditNoteEmails,
                    projectEmails,
                    ticketEmails,
                    taskEmails,
                    contractEmails,
                )
                if (response?.isSuccessful == true) {
                    if (response.body() != null) {
                        withContext(Dispatchers.Main) {
                            responseMessage.value = response.body()?.message
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
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    responseMessage.value = e.message.toString()
                    loading.value = false
                }
            }
        }
    }
}