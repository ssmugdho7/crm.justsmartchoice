package com.divesh.perfex.leads.domain.interfaces

import com.divesh.perfex.leads.domain.models.Lead

interface ManageLeadsInterface {
    fun onLeadDeleteClicked(leadId: Int, position: Int)
    fun onLeadEditClicked(lead: Lead)
    fun onLeadViewClicked(lead: Lead)
    fun onDialCallClicked(phoneNumber: String?)
    fun onSendMailClicked(email: String?)
}