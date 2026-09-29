package com.divesh.perfex.sales.data.repositories

import com.divesh.perfex.core.api.ApiInterface
import okhttp3.MultipartBody
import okhttp3.RequestBody

class SalesApiRepository(private val apiService: ApiInterface) {
    suspend fun getProposals(staffId: String, relId: Int?, relType: String?) =
        apiService.getProposals(staffId, relId, relType)

    suspend fun getRelItems(staffId: String, relId: Int, relType: String) =
        apiService.getRelItems(staffId, relId, relType)

    /* suspend fun deleteProposal(staffId: String, proposalId: Int) =
         apiService.deleteProposal(staffId, proposalId)*/

    suspend fun getProposalComments(staffId: String, proposalId: Int) =
        apiService.getProposalComments(staffId, proposalId)

    suspend fun getProposalInitialData(staffId: String) = apiService.getProposalInitialData(staffId)
    suspend fun addProposal(
        staffId: String,
        subject: String,
        relType: String,
        relId: Int,
        projectId: String?,
        date: String,
        openTill: String?,
        currency: Int,
        discountType: String?,
        tags: String?,
        allowComments: String?,
        status: Int,
        assigned: Int,
        proposalTo: String?,
        address: String?,
        city: String,
        state: String?,
        country: Int,
        zip: String?,
        email: String,
        phone: String,
        itemSelected: String?,
        show_quantity_as: Int?,
        description: String?,
        longDescription: String?,
        quantity: Int,
        unit: String?,
        rate: Double?,
        taxName: String?,
        addedItems: String,
        subTotal: Double,
        discountPercent: Double,
        discountTotal: Double?,
        adjustment: Double?,
        total: Double,
        saveAndSend: Boolean?
    ) = apiService.addProposal(
        staffId,
        subject,
        relType,
        relId,
        projectId,
        date,
        openTill,
        currency,
        discountType,
        tags,
        allowComments,
        status,
        assigned,
        proposalTo,
        address,
        city,
        state,
        country,
        zip,
        email,
        phone,
        itemSelected,
        show_quantity_as,
        description,
        longDescription,
        quantity,
        unit,
        rate,
        taxName,
        addedItems,
        subTotal,
        discountPercent,
        discountTotal,
        adjustment,
        total,
        saveAndSend
    )

    suspend fun getEstimates(staffId: String, relId: Int?, relType: String?) =
        apiService.getEstimates(staffId, relId, relType)

    suspend fun viewTask(staffId: String, taskId: Int) = apiService.viewTask(staffId, taskId)
    suspend fun updateTaskFollowers(staffId: String, followerIds: String, taskId: String) =
        apiService.updateTaskFollowers(staffId, followerIds, taskId)

    suspend fun updateTaskAssignees(staffId: String, assigneeIds: String, taskId: String) =
        apiService.updateTaskAssignees(staffId, assigneeIds, taskId)

    suspend fun removeAttachment(staffId: String, attachmentId: Int) =
        apiService.removeAttachment(staffId, attachmentId)

    suspend fun addAttachment(
        staffId: RequestBody,
        attachment: MultipartBody.Part?,
        taskId: RequestBody
    ) = apiService.addAttachment(staffId, taskId, attachment)

    suspend fun removeComment(staffId: String, commentId: Int) =
        apiService.removeComment(staffId, commentId)

    suspend fun updateComment(staffId: String, comment: String, commentId: String) =
        apiService.updateComment(staffId, comment, commentId)

    suspend fun addComment(
        staffId: RequestBody,
        taskId: RequestBody,
        comment: RequestBody,
        attachment: MultipartBody.Part?
    ) = apiService.addComment(staffId, taskId, comment, attachment)

    suspend fun removeChecklistItem(staffId: String, checkListItemId: Int) =
        apiService.removeChecklistItem(staffId, checkListItemId)

    suspend fun onChecklistItemToggled(staffId: String, checkListItemId: Int, is_completed: Int) =
        apiService.onChecklistItemToggled(staffId, checkListItemId, is_completed)

    suspend fun saveTemplate(staffId: String, description: String) =
        apiService.saveChecklistAsTemplate(staffId, description)

    suspend fun addCheckListItem(staffId: String, description: String, taskId: String) =
        apiService.addCheckListItem(staffId, description, taskId)

    suspend fun updateCheckListItem(staffId: String, description: String, checklistItemId: Int) =
        apiService.updateCheckListItem(staffId, description, checklistItemId)

    suspend fun assignStaffToChecklist(
        staffId: String,
        assignedTo: Int,
        checklistItemId: Int,
        taskId: Int
    ) = apiService.assignStaffToChecklist(staffId, assignedTo, checklistItemId, taskId)

    suspend fun getInvoices(staffId: String, relId: Int?, relType: String?) =
        apiService.getInvoices(staffId, relId, relType)

    suspend fun viewInvoice(staffId: String, invoiceId: Int?) =
        apiService.viewInvoice(staffId, invoiceId)

    suspend fun markInvoiceAsSent(staffId: String, invoiceId: Int?) =
        apiService.markInvoiceAsSent(staffId, invoiceId)

    suspend fun getInitialInvoiceData(staffId: String) = apiService.getInitialInvoiceData(staffId)
    suspend fun addInvoice(
        authentication_token: String,
        cancel_merged_invoices: String,
        clientid: Int,
        project_id: Int?,
        billing_street: String?,
        billing_city: String?,
        billing_state: String?,
        billing_zip: String?,
        show_shipping_on_invoice: String?,
        shipping_street: String?,
        shipping_city: String?,
        shipping_state: String?,
        shipping_zip: String?,
        number: String?,
        date: String,
        duedate: String?,
        tags: String?,
        allowed_payment_modes: String,
        currency: Int,
        sale_agent: Int?,
        recurring: Int,
        discount_type: String?,
        repeat_every_custom: Int?,
        repeat_type_custom: String?,
        adminnote: String?,
        item_select: String?,
        show_quantity_as: Int?,
        description: String?,
        long_description: String?,
        quantity: Int?,
        unit: String?,
        rate: Double?,
        newitems: String?,
        subtotal: Double?,
        discount_percent: Double?,
        discount_total: Double?,
        adjustment: Double?,
        total: Double?,
        task_id: Int?,
        expense_id: Int?,
        clientnote: String?,
        terms: String?,
        save_and_send: Boolean
    ) = apiService.addInvoice(
        authentication_token,
        cancel_merged_invoices,
        clientid,
        project_id,
        billing_street,
        billing_city,
        billing_state,
        billing_zip,
        show_shipping_on_invoice,
        shipping_street,
        shipping_city,
        shipping_state,
        shipping_zip,
        number,
        date,
        duedate,
        tags,
        allowed_payment_modes,
        currency,
        sale_agent,
        recurring,
        discount_type,
        repeat_every_custom,
        repeat_type_custom,
        adminnote,
        item_select,
        show_quantity_as,
        description,
        long_description,
        quantity,
        unit,
        rate,
        newitems,
        subtotal,
        discount_percent,
        discount_total,
        adjustment,
        total,
        task_id,
        expense_id,
        clientnote,
        terms,
        save_and_send
    )
}