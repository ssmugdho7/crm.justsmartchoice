package com.divesh.perfex.leads.domain.models

data class LeadsStatus(
    val message: String? = "",
    val status: List<Status> = listOf()
) {
    data class Status(
        val color: String? = "",
        val id: Int = 0,
        val isdefault: Int = 0,
        val name: String? = "",
        val statusorder: Int = 0
    )
}