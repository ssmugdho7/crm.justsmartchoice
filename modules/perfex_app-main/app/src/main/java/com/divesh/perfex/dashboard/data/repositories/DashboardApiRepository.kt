package com.divesh.perfex.dashboard.data.repositories

import com.divesh.perfex.core.api.ApiInterface

class DashboardApiRepository(private val apiService: ApiInterface) {
    suspend fun getDashboardData(staffId: String) = apiService.getDashboardData(staffId)
}