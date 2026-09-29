package com.divesh.perfex.notifications.data.repositories

import com.divesh.perfex.core.api.ApiInterface

class NotificationsApiRepository(private val apiService: ApiInterface) {
    suspend fun getNotifications(staffId: String, startFrom: Int, endTo: Int) =
        apiService.getNotifications(staffId, startFrom, endTo)

    suspend fun markNotificationAsRead(staffId: String, notificationId: Int) =
        apiService.markNotificationAsRead(staffId, notificationId)

    suspend fun getLatestNotification(staffId: String) =
        apiService.getLatestNotification(staffId)
}