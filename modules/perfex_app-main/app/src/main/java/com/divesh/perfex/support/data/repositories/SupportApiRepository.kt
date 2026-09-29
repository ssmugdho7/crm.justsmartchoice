package com.divesh.perfex.support.data.repositories

import com.divesh.perfex.core.api.ApiInterface
import okhttp3.MultipartBody
import okhttp3.RequestBody

class SupportApiRepository(private val apiService: ApiInterface) {
    suspend fun getTickets(staffId: String, startFrom: Int, endTo: Int) =
        apiService.getTickets(staffId, startFrom, endTo)

    suspend fun addTicket(
       staffId: String,
        subject: String,
        contactId: Int,
        name: String?,
        email: String?,
        departmentId: Int,
        cc: String?,
        tags: String?,
        assigned: Int?,
        priority: Int,
        service: Int?,
        project_id: Int?,
        message: String
    ) = apiService.addTicket(
        staffId,
        subject,
        contactId,
        name,
        email,
        departmentId,
        cc,
        tags,
        assigned,
        priority,
        service,
        project_id,
        message
    )

    suspend fun updateTicket(
       staffId: String,
        ticketId: Int,
        subject: String,
        contactId: Int,
        name: String?,
        email: String?,
        departmentId: Int,
        cc: String?,
        tags: String?,
        assigned: Int?,
        priority: Int,
        service: Int?,
        project_id: Int?,
        message: String
    ) = apiService.updateTicket(
        staffId,
        ticketId,
        subject,
        contactId,
        name,
        email,
        departmentId,
        cc,
        tags,
        assigned,
        priority,
        service,
        project_id,
        message
    )

    suspend fun deleteTicket(staffId: String, ticketId: Int) =
        apiService.deleteTicket(staffId, ticketId)

    suspend fun getInitialData(staffId: String) = apiService.getTicketInitialData(staffId)
    suspend fun getTicketReplies(staffId: String, ticketId: Int) = apiService.getTicketReplies(staffId, ticketId)
    suspend fun addTicketReply(staffId: RequestBody, ticketId: RequestBody, message: RequestBody, image: MultipartBody.Part?) = apiService.addTicketReply(staffId, ticketId, message, image)
}