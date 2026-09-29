package com.divesh.perfex.login.presentation

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants.Companion.authenticationToken
import com.divesh.perfex.core.constants.Constants.Companion.emailId
import com.divesh.perfex.core.constants.Constants.Companion.firstName
import com.divesh.perfex.core.constants.Constants.Companion.isAdmin
import com.divesh.perfex.core.constants.Constants.Companion.isRegisteredOnPusher
import com.divesh.perfex.core.constants.Constants.Companion.lastName
import com.divesh.perfex.core.constants.Constants.Companion.loginStatus
import com.divesh.perfex.core.constants.Constants.Companion.phoneNumber
import com.divesh.perfex.core.constants.Constants.Companion.profileImage
import com.divesh.perfex.core.constants.Constants.Companion.staffId
import com.divesh.perfex.login.data.repositories.LoginApiRepository
import com.divesh.perfex.login.domain.models.MetaDataResponse
import com.divesh.perfex.login.domain.models.Staff
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.*
import javax.inject.Inject

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val repository: LoginApiRepository,
    private val sharedPreferences: SharedPreferences
) : ViewModel() {
    val isLoggedIn = MutableLiveData<Boolean>()
    val errorMessage = MutableLiveData<String>()
    val metaDataResponse = MutableLiveData<MetaDataResponse>()

    init {
        isLoggedIn("light")
    }

    private fun isLoggedIn(type: String) {
        isLoggedIn.value = sharedPreferences.getBoolean(loginStatus, false)
        if (!isLoggedIn.value!!) {
            loadCrmMetaData(type)
        }
    }

    private fun loadCrmMetaData(type: String) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.loadCrmMetaData(type)
                if(response?.isSuccessful == true){
                    withContext(Dispatchers.Main){
                        metaDataResponse.value = response.body()
                    }
                }
            } catch (exception: Exception) {
                exception.printStackTrace()
            }
        }
    }

    private fun setDataAfterLogin(staff: Staff) {
        sharedPreferences.edit().putBoolean(loginStatus, true).apply()
        sharedPreferences.edit().putBoolean(isRegisteredOnPusher, false).apply()
        sharedPreferences.edit().putString(emailId, staff.email).apply()
        sharedPreferences.edit().putString(firstName, staff.firstname).apply()
        sharedPreferences.edit().putString(lastName, staff.lastname).apply()
        sharedPreferences.edit().putString(authenticationToken, staff.authentication_token).apply()
        sharedPreferences.edit().putInt(staffId, staff.staffid).apply()
        if (staff.profile_image != null) {
            sharedPreferences.edit().putString(profileImage, staff.profile_image).apply()
        }
        if (staff.phonenumber != null) {
            sharedPreferences.edit().putString(phoneNumber, staff.phonenumber).apply()
        }
        if (staff.admin == 1)
            sharedPreferences.edit().putBoolean(isAdmin, true).apply()
        else
            sharedPreferences.edit().putBoolean(isAdmin, false).apply()
    }

    fun login(email: String, password: String) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = repository.loginUser(email, password)
                if (response?.isSuccessful == true) {
                    if (response.body()?.status == 1) {
                        setDataAfterLogin(response.body()!!.data)
                        withContext(Dispatchers.Main) {
                            isLoggedIn.value = true
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            errorMessage.value = response.body()?.message
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        errorMessage.value = "An error occurred"
                    }
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    errorMessage.value = e.message.toString()
                }
            }
        }
    }
}