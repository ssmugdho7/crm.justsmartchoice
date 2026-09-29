package com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.add

import android.content.SharedPreferences
import com.divesh.perfex.core.base.BaseViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.customers.data.repositories.CustomersApiRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.HttpException
import java.net.SocketTimeoutException
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel class AddContactViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val apiRepository: CustomersApiRepository,
) : BaseViewModel() {
    fun addContact(
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
        doNotSendWelcomeEmail: String?,
        sendSetPasswordEmail: String?,
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
                val response = apiRepository.addCustomerContact(
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
                    doNotSendWelcomeEmail,
                    sendSetPasswordEmail,
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
                            loading.value = false
                            responseMessage.value = response.body()?.message
                            if (response.body()?.status == 1) {
                                itemAdded.value = true
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
            }catch (socketTimeOutException: SocketTimeoutException) {
                withContext(Dispatchers.Main) {
                    internetProblem.value = true
                }
            } catch (unknownHostException: UnknownHostException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = "Internet connection is not stable!"
                }
            } catch (httpException: HttpException) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    internetProblem.value = true
                }
            } catch (exception: Exception) {
                withContext(Dispatchers.Main) {
                    loading.value = false
                    responseMessage.value = exception.message.toString()
                }
            }
        }
    }
}