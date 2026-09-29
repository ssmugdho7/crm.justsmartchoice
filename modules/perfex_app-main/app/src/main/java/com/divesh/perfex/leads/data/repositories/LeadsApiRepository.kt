package com.divesh.perfex.leads.data.repositories

import com.divesh.perfex.core.api.ApiInterface

class LeadsApiRepository(private val apiService: ApiInterface) {
    //leads
    suspend fun getLeads(staffId: String, startFrom: Int, endTo: Int) =
        apiService.getLeads(staffId, startFrom, endTo)

    suspend fun getLead(staffId: String, leadId: String) =
        apiService.getLead(staffId, leadId)

    suspend fun addLead(
        staffId: String,
        status: String,
        source: String,
        assigned: String,
        name: String,
        position: String,
        email: String,
        website: String,
        phone: String,
        company: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zipCode: String,
        description: String,
        isPublic: Boolean,
        lastContact: String,
        contactedToday: String?,
        leadValue: String,
        customFields: String?
    ) = apiService.addLead(
        staffId,
        status,
        source,
        assigned,
        name,
        position,
        email,
        website,
        phone,
        company,
        address,
        city,
        state,
        country,
        zipCode,
        description,
        isPublic,
        lastContact,
        contactedToday,
        leadValue,
        customFields
    )

    suspend fun updateLead(
        staffId: String,
        status: String,
        source: String,
        assigned: String,
        name: String,
        position: String,
        email: String,
        website: String,
        phone: String,
        company: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zipCode: String,
        description: String,
        isPublic: Boolean,
        lastContact: String,
        leadValue: String,
        leadId: String,
        customFields: String?
    ) = apiService.updateLead(
        staffId,
        status,
        source,
        assigned,
        name,
        position,
        email,
        website,
        phone,
        company,
        address,
        city,
        state,
        country,
        zipCode,
        description,
        isPublic,
        lastContact,
        leadValue,
        leadId,
        customFields
    )

    suspend fun getLeadInitialFormData(staffId: String) = apiService.getLeadInitialFormData(staffId)

    suspend fun deleteLead(staffId: String, leadId: Int) = apiService.deleteLead(staffId, leadId)

    //lead pages
    suspend fun getTasks(
        staffId: String, startFrom: Int, endTo: Int, rel_id: Int, rel_type: String
    ) = apiService.getTasks(staffId, startFrom, endTo, rel_id, rel_type)

    suspend fun deleteTask(staffId: String, taskId: Int) = apiService.deleteTask(staffId, taskId)
    suspend fun updateTaskStatus(staffId: String, taskId: Int, status: Int) =
        apiService.updateTaskStatus(staffId, taskId, status)

    suspend fun startOrStopTask(
        staffId: String, taskId: Int, not_finished_timer_by_current_staff: Int
    ) = apiService.startOrStopTask(staffId, taskId, not_finished_timer_by_current_staff)

    suspend fun addTask(
        staffId: String,
        billable: String?,
        name: String,
        hourly_rate: String,
        milestone: String,
        startDate: String,
        dueDate: String,
        priority: String,
        repeat_every: String,
        repeat_every_custom: String,
        repeat_type_custom: String,
        cycles: String,
        rel_type: String?,
        rel_id: Int,
        tags: String,
        description: String
    ) = apiService.addTask(
        staffId,
        billable,
        name,
        hourly_rate,
        milestone,
        startDate,
        dueDate,
        priority,
        repeat_every,
        repeat_every_custom,
        repeat_type_custom,
        cycles,
        rel_type,
        rel_id,
        tags,
        description
    )

    suspend fun updateTask(
        staffId: String,
        billable: String?,
        name: String,
        hourly_rate: String,
        milestone: String,
        startDate: String,
        dueDate: String,
        priority: String,
        repeat_every: String,
        repeat_every_custom: String,
        repeat_type_custom: String,
        cycles: String,
        rel_type: String,
        rel_id: String,
        tags: String,
        description: String,
        taskId: Int
    ) = apiService.updateTask(
        staffId,
        billable,
        name,
        hourly_rate,
        milestone,
        startDate,
        dueDate,
        priority,
        repeat_every,
        repeat_every_custom,
        repeat_type_custom,
        cycles,
        rel_type,
        rel_id,
        tags,
        description,
        taskId
    )

    suspend fun getRelatedToItems(staffId: String) = apiService.getRelatedToItems(staffId)
    suspend fun getLeadNotes(
        staffId: String, startFrom: Int, endTo: Int, leadId: String, relType: String
    ) = apiService.getLeadNotes(staffId, startFrom, endTo, leadId, relType)

    suspend fun deleteNotes(staffId: String, noteId: Int) = apiService.deleteNotes(staffId, noteId)
    suspend fun addNote(
        staffId: String, description: String, date: String, relType: String, leadId: String
    ) = apiService.addNotes(staffId, description, date, relType, leadId)

    suspend fun editNote(staffId: String, description: String, id: Int) =
        apiService.editNote(staffId, description, id)

    suspend fun getLeadReminders(
        staffId: String, startFrom: Int, endTo: Int, rel_id: String, rel_type: String
    ) = apiService.getLeadReminders(staffId, startFrom, endTo, rel_id, rel_type)

    suspend fun getAssignees(staffId: String) = apiService.getAssignee(staffId)
    suspend fun addReminder(
        staffId: String,
        notifyByEmail: Int,
        description: String,
        date: String,
        relType: String,
        relId: String
    ) = apiService.addReminder(staffId, notifyByEmail, description, date, relType, relId)

    suspend fun editReminder(
        staffId: String, notifyByEmail: Int, description: String, date: String, id: Int
    ) = apiService.editReminder(staffId, notifyByEmail, description, date, id)

    suspend fun deleteReminder(staffId: String, id: Int) = apiService.deleteReminder(staffId, id)
    suspend fun getLeadActivityLog(staffId: String, id: Int) =
        apiService.getLeadActivityLog(staffId, id)

    suspend fun getCountryList(staffId: String) = apiService.getCountryList(staffId)
    suspend fun addLeadActivityLog(staffId: String, id: Int, description: String) =
        apiService.addLeadActivityLog(staffId, id, description)

    //convert to customer
    suspend fun convertToCustomer(
        staffId: String,
        leadId: Int,
        firstname: String,
        lastname: String,
        title: String,
        email: String,
        company: String,
        phoneNumber: String,
        website: String,
        address: String,
        city: String,
        state: String,
        country: String,
        zip: String,
        password: String,
        send_set_password_email: String,
        do_not_send_welcome_email: String,
        original_lead_email: String
    ) = apiService.convertToCustomer(
        staffId,
        leadId,
        firstname,
        lastname,
        title,
        email,
        company,
        phoneNumber,
        website,
        address,
        city,
        state,
        country,
        zip,
        password,
        send_set_password_email,
        do_not_send_welcome_email,
        original_lead_email
    )
}