package com.divesh.perfex.notifications.domain.interfaces

import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel

interface NotificationInterface {
    fun markAsRead(notification: NotificationsResponseModel.Notification)
}