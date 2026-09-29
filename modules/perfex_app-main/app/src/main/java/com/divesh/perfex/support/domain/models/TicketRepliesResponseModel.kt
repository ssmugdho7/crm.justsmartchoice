package com.divesh.perfex.support.domain.models

data class TicketRepliesResponseModel(
    val message: String,
    val replies: List<Reply>,
    val status: Int
){
    data class Reply(
        val admin: Int?,
        val attachment: String?,
        val contactid: Int?,
        val date: String,
        val email: String,
        val fileName: String?,
        val fileType: String?,
        val id: Int,
        val message: String?,
        val name: String,
        val ticketid: Int,
        val userid: Int
    )
}