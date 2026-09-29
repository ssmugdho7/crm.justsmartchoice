package com.divesh.perfex.leads.domain.interfaces

import com.divesh.perfex.leads.domain.models.RemindersResponseModel

interface LeadReminderInterface {
    fun deleteReminder(id: Int, position: Int)
    fun editReminder(reminder: RemindersResponseModel.Reminder)
}