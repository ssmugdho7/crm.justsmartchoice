package com.divesh.perfex.login.data.repositories

import com.divesh.perfex.core.api.ApiInterface

class LoginApiRepository(private val apiService: ApiInterface) {
    suspend fun loginUser(email: String, password: String) = apiService.loginUser(email, password)
    suspend fun loadCrmMetaData(type: String) = apiService.loadCrmMetaData(type)
}