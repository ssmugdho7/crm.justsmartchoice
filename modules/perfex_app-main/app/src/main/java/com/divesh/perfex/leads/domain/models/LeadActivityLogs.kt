package com.divesh.perfex.leads.domain.models

data class LeadActivityLogs(
    val logs: List<Log> = listOf(),
    val status: Int = 0
){
    data class Log(
        val additional_data: String? = "",
        val date: String? = "",
        val time_ago: String? = ""
    )
}
