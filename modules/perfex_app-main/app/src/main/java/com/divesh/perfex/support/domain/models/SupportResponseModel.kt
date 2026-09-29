package com.divesh.perfex.support.domain.models

data class SupportResponseModel(
    val message: String,
    val status: Int,
    val tickets: List<Ticket>
){
    data class Ticket(
        val admin: Int,
        val adminread: Int,
        val adminreplying: Int,
        val assigned: Int,
        val cc: String,
        val clientread: Int,
        val contactid: Int,
        val date: String,
        val department: Int,
        val departmentName: String?,
        val email: String?,
        val lastreply: String?,
        val merged_ticket_id: String,
        val message: String?,
        val name: String?,
        val priority: Int,
        val project_id: Int,
        val service: Int,
        val staff_id_replying: String,
        val status: Int,
        val subject: String,
        val ticketid: Int,
        val ticketkey: String,
        val userid: Int
    ): java.io.Serializable
}