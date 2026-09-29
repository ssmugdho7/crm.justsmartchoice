package com.divesh.perfex.core.view_models

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel class MainActivityViewModel @Inject constructor(
    private val sharedPreferences: SharedPreferences
): ViewModel() {
    val name = MutableLiveData<String>()
    val email = MutableLiveData<String>()
    val staffId = MutableLiveData<Int>()
    val profileImage = MutableLiveData<String>()
    val loggedIn = MutableLiveData<Boolean>()
    init {
        currentUser()
    }

    private fun currentUser() {
        name.value = "Welcome! ${sharedPreferences.getString(Constants.firstName, "")} ${sharedPreferences.getString(Constants.lastName, "")}"
        email.value = sharedPreferences.getString(Constants.emailId, "")
        staffId.value = sharedPreferences.getInt(Constants.staffId, 0)
        profileImage.value = sharedPreferences.getString(Constants.profileImage, "")
    }

    fun logoutUser(){
        sharedPreferences.edit().putBoolean(Constants.loginStatus, false).apply()
        sharedPreferences.edit().putString(Constants.authenticationToken, null).apply()
        sharedPreferences.edit().putString(Constants.emailId, null).apply()
        sharedPreferences.edit().putString(Constants.firstName, null).apply()
        sharedPreferences.edit().putString(Constants.lastName, null).apply()
        sharedPreferences.edit().putInt(Constants.staffId, 0).apply()
        sharedPreferences.edit().putString(Constants.profileImage, null).apply()
        sharedPreferences.edit().putInt(Constants.phoneNumber, 0).apply()
        sharedPreferences.edit().putBoolean(Constants.isAdmin, false).apply()
        loggedIn.value = false
    }
}