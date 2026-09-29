package com.divesh.perfex.dashboard.presentation

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.dashboard.data.repositories.DashboardApiRepository
import com.divesh.perfex.dashboard.domain.models.DashboardDataResponseModel
import com.divesh.perfex.login.domain.models.Staff
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.net.UnknownHostException
import javax.inject.Inject

@HiltViewModel
class DashboardViewModel @Inject constructor(
    private val preferencesFile: SharedPreferences,
    private val dashboardApiRepository: DashboardApiRepository
) : ViewModel() {
    val currentRecords = MutableLiveData<DashboardDataResponseModel>()
    val loadError = MutableLiveData<String?>()
    val loading = MutableLiveData<Boolean>()

    fun refresh() {
        loading.value = true
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = dashboardApiRepository.getDashboardData(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response != null) {
                    if (response.isSuccessful) {
                        withContext(Dispatchers.Main) {
                            response.body()?.optionsData?.let {
                                savePusherConfigurations(response.body()?.optionsData!!)
                            }
                            updateProfileData(response.body()?.currentUser)
                            currentRecords.value = response.body()
                            loadError.value = null
                            loading.value = false
                        }
                    } else {
                        withContext(Dispatchers.Main) {
                            loading.value = false
                            loadError.value = response.errorBody().toString()
                        }
                    }
                } else {
                    withContext(Dispatchers.Main) {
                        loading.value = false
                        loadError.value = "An error occurred"
                    }
                }
            }catch (unknownHostException: UnknownHostException){
                unknownHostException.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = "Internet connection is not stable!"
                }
            } catch (e: Exception) {
                e.printStackTrace()
                withContext(Dispatchers.Main) {
                    loading.value = false
                    loadError.value = e.message.toString()
                }
            }
        }
    }

    private fun updateProfileData(staff: Staff?) {
        if(staff != null){
            preferencesFile.edit().putString(Constants.emailId, staff.email).apply()
            preferencesFile.edit().putString(Constants.firstName, staff.firstname).apply()
            preferencesFile.edit().putString(Constants.lastName, staff.lastname).apply()
            if (staff.profile_image != null) {
                preferencesFile.edit().putString(Constants.profileImage, staff.profile_image).apply()
            }
            if (staff.phonenumber != null) {
                preferencesFile.edit().putString(Constants.phoneNumber, staff.phonenumber).apply()
            }
            if (staff.admin == 1)
                preferencesFile.edit().putBoolean(Constants.isAdmin, true).apply()
            else
                preferencesFile.edit().putBoolean(Constants.isAdmin, false).apply()
        }
    }

    private fun savePusherConfigurations(optionsData: List<DashboardDataResponseModel.OptionsData>) {
        var pusherRealtimeNotifications = ""
        var pusherAppKey = ""
        var pusherCluster = ""
        optionsData.forEach {
            when (it.name) {
                "pusher_cluster" -> {
                    pusherCluster = it.value.toString()
                }
                "pusher_realtime_notifications" -> {
                    pusherRealtimeNotifications = it.value.toString()
                }
                "pusher_app_key" -> {
                    pusherAppKey = it.value.toString()
                }
            }
        }
        if(pusherRealtimeNotifications != "" && pusherAppKey != "" && pusherCluster != ""){
            preferencesFile.edit().putString(Constants.isRegisteredOnPusher, pusherRealtimeNotifications).apply()
            preferencesFile.edit().putString(Constants.pusherAppKey, pusherAppKey).apply()
            preferencesFile.edit().putString(Constants.pusherCluster, pusherCluster).apply()
        }
    }
}