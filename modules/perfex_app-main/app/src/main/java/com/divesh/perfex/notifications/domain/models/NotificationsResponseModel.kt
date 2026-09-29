package com.divesh.perfex.notifications.domain.models

data class NotificationsResponseModel(
    val message: String,
    val notifications: List<Notification>,
    val status: Int
){
    data class Notification(
        val additional_data: String?,
        val date: String,
        val description: String,
        val from_fullname: String?,
        val fromclientid: Int,
        val fromcompany: String?,
        val fromuserid: Int,
        val full_date: String,
        val id: Int,
        val isread: Int,
        val isread_inline: Int,
        val link: String?,
        val profile_image: String?,
        val touserid: Int
    )
}