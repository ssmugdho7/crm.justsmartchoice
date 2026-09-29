package com.divesh.perfex.leads.domain.models

data class RemindersResponseModel(
    val reminders: List<Reminder> = listOf(),
    val status: Int = 0
){
    data class Reminder(
        val creator: Int = 0,
        val date: String? = "",
        val description: String? = "",
        val id: Int = 0,
        val isnotified: Int = 0,
        val notify_by_email: Int = 0,
        val rel_id: Int = 0,
        val rel_type: String? = "",
        val staff: Int = 0,
        val firstname: String? = "",
        val lastname: String? = ""
    )
}
