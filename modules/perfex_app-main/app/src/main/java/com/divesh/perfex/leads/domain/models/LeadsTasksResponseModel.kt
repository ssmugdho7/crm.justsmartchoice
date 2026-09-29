package com.divesh.perfex.leads.domain.models

data class LeadsTasksResponseModel(
    val status: Int = 0,
    val message: String? = "",
    val tasks: List<Task> = listOf()
)