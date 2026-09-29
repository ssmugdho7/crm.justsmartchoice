package com.divesh.perfex.dashboard.domain.models

import com.divesh.perfex.login.domain.models.Staff

data class DashboardDataResponseModel(
    val contactsData: ContactsData,
    val customerData: CustomerData,
    val leadsData: LeadsData,
    val message: String,
    val status: Int,
    val tasksData: TasksData,
    val ticketsData: TicketsData,
    val currentUser: Staff,
    val optionsData: List<OptionsData>
){
    data class ContactsData(
        val activeContacts: Int,
        val inActiveContacts: Int,
        val totalContacts: Int
    )

    data class CustomerData(
        val activeCustomers: Int,
        val totalCustomers: Int
    )

    data class LeadsData(
        val convertedLeads: Int,
        val totalLeads: Int
    )

    data class TasksData(
        val tasksNotFinished: Int,
        val totalTasks: Int
    )

    data class TicketsData(
        val highPriorityTickets: Int,
        val lowPriorityTickets: Int,
        val mediumPriorityTickets: Int,
        val openTickets: Int,
        val ticketsWithoutContact: Int,
        val totalTickets: Int
    )

    data class OptionsData(
        val id: Int,
        val name: String,
        val value: String?,
        val autoload: Int
    )
}