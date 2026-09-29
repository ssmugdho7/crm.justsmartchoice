package com.divesh.perfex.leads.domain.models

data class AssigneeList(
    val assignees: List<Assignee> = listOf(),
    val message: String? = "",
    val status: Int = 0
){
    data class Assignee(
        val firstname: String? = "",
        val lastname: String? = "",
        val staffid: Int = 0
    )
}