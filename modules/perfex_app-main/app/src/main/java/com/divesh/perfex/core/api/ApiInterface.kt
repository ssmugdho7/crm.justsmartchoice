package com.divesh.perfex.core.api

import com.divesh.perfex.customers.domain.models.*
import com.divesh.perfex.dashboard.domain.models.DashboardDataResponseModel
import com.divesh.perfex.leads.domain.models.*
import com.divesh.perfex.login.domain.models.LoginResponse
import com.divesh.perfex.login.domain.models.MetaDataResponse
import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel
import com.divesh.perfex.sales.domain.models.*
import com.divesh.perfex.sales.domain.models.invoices.InitialInvoiceResponseModel
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse
import com.divesh.perfex.sales.domain.models.invoices.ViewInvoiceResponseModel
import com.divesh.perfex.sales.domain.models.proposals.InitialProposalResponseModel
import com.divesh.perfex.sales.domain.models.proposals.ProposalCommentsResponseModel
import com.divesh.perfex.sales.domain.models.proposals.ProposalItemsResponseModel
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import com.divesh.perfex.support.domain.models.InitialTicketResponseModel
import com.divesh.perfex.support.domain.models.SupportResponseModel
import com.divesh.perfex.support.domain.models.TicketRepliesResponseModel
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface ApiInterface {
    @FormUrlEncoded
    @POST("crm_meta_data")
    suspend fun loadCrmMetaData(
        @Field("type") type: String?,
    ): Response<MetaDataResponse?>?

    @FormUrlEncoded
    @POST("login")
    suspend fun loginUser(
        @Field("email") email: String?,
        @Field("password") password: String?
    ): Response<LoginResponse?>?

    @FormUrlEncoded
    @POST("get_my_leads")
    suspend fun getLeads(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") start_from: Int,
        @Field("end_to") end_to: Int
    ): Response<LeadsResponseModel?>?

    @FormUrlEncoded
    @POST("get_lead")
    suspend fun getLead(
        @Field("authentication_token") authenticationToken: String,
        @Field("lead_id") lead_id: String
    ): Response<ViewLeadResponseModel?>?

    @FormUrlEncoded
    @POST("delete_my_lead")
    suspend fun deleteLead(
        @Field("authentication_token") authenticationToken: String,
        @Field("lead_id") leadId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_my_tasks")
    suspend fun getTasks(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") start_from: Int,
        @Field("end_to") end_to: Int,
        @Field("rel_id") rel_id: Int,
        @Field("rel_type") rel_type: String
    ): Response<LeadsTasksResponseModel?>?

    @FormUrlEncoded
    @POST("delete_task")
    suspend fun deleteTask(
        @Field("authentication_token") authenticationToken: String,
        @Field("taskid") taskId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("change_task_status")
    suspend fun updateTaskStatus(
        @Field("authentication_token") authenticationToken: String,
        @Field("taskid") leadid: Int,
        @Field("status") status: Int,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("start_or_stop_task")
    suspend fun startOrStopTask(
        @Field("authentication_token") authenticationToken: String,
        @Field("taskid") leadid: Int,
        @Field("not_finished_timer_by_current_staff") not_finished_timer_by_current_staff: Int,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("add_task")
    suspend fun addTask(
        @Field("authentication_token") authenticationToken: String,
        @Field("billable") billable: String?,
        @Field("name") name: String,
        @Field("hourly_rate") hourly_rate: String,
        @Field("milestone") milestone: String,
        @Field("startdate") startDate: String,
        @Field("duedate") dueDate: String,
        @Field("priority") priority: String,
        @Field("repeat_every") repeat_every: String,
        @Field("repeat_every_custom") repeat_every_custom: String,
        @Field("repeat_type_custom") repeat_type_custom: String,
        @Field("cycles") cycles: String,
        @Field("rel_type") rel_type: String?,
        @Field("rel_id") rel_id: Int,
        @Field("tags") tags: String,
        @Field("description") description: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("add_task")
    suspend fun updateTask(
        @Field("authentication_token") authenticationToken: String,
        @Field("billable") billable: String?,
        @Field("name") name: String,
        @Field("hourly_rate") hourly_rate: String,
        @Field("milestone") milestone: String,
        @Field("startdate") startDate: String,
        @Field("duedate") dueDate: String?,
        @Field("priority") priority: String,
        @Field("repeat_every") repeat_every: String,
        @Field("repeat_every_custom") repeat_every_custom: String,
        @Field("repeat_type_custom") repeat_type_custom: String,
        @Field("cycles") cycles: String,
        @Field("rel_type") rel_type: String,
        @Field("rel_id") rel_id: String,
        @Field("tags") tags: String,
        @Field("description") description: String,
        @Field("id") id: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_related_type")
    suspend fun getRelatedToItems(
        @Field("authentication_token") authenticationToken: String
    ): Response<RelatedTo?>?

    @FormUrlEncoded
    @POST("get_lead_notes")
    suspend fun getLeadNotes(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") startFrom: Int?,
        @Field("end_to") endTo: Int?,
        @Field("rel_id") relId: String,
        @Field("rel_type") relType: String
    ): Response<LeadNotes?>?

    @FormUrlEncoded
    @POST("delete_notes")
    suspend fun deleteNotes(
        @Field("authentication_token") authenticationToken: String,
        @Field("id") id: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("edit_notes")
    suspend fun editNote(
        @Field("authentication_token") authenticationToken: String,
        @Field("description") description: String,
        @Field("id") id: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("add_notes")
    suspend fun addNotes(
        @Field("authentication_token") authenticationToken: String,
        @Field("description") description: String,
        @Field("date_contacted") date_contacted: String,
        @Field("rel_type") rel_type: String,
        @Field("rel_id") rel_id: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_reminders")
    suspend fun getLeadReminders(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") startFrom: Int?,
        @Field("end_to") endTo: Int?,
        @Field("rel_id") relId: String,
        @Field("rel_type") relType: String
    ): Response<RemindersResponseModel?>?

    @FormUrlEncoded
    @POST("get_assginees")
    suspend fun getAssignee(
        @Field("authentication_token") authenticationToken: String
    ): Response<AssigneeList?>?

    @FormUrlEncoded
    @POST("delete_reminder")
    suspend fun deleteReminder(
        @Field("authentication_token") authenticationToken: String,
        @Field("id") id: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("edit_reminders")
    suspend fun editReminder(
        @Field("authentication_token") authenticationToken: String,
        @Field("notify_by_email") notify_by_email: Int,
        @Field("description") description: String,
        @Field("date") date: String,
        @Field("id") id: Int,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("add_reminders")
    suspend fun addReminder(
        @Field("authentication_token") authenticationToken: String,
        @Field("notify_by_email") notify_by_email: Int,
        @Field("description") description: String,
        @Field("date") date: String,
        @Field("rel_type") rel_type: String,
        @Field("rel_id") rel_id: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_lead_activity_log")
    suspend fun getLeadActivityLog(
        @Field("authentication_token") authenticationToken: String,
        @Field("leadid") leadId: Int
    ): Response<LeadActivityLogs?>?

    @FormUrlEncoded
    @POST("add_lead_activity_log")
    suspend fun addLeadActivityLog(
        @Field("authentication_token") authenticationToken: String,
        @Field("leadid") leadId: Int,
        @Field("description") description: String,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("convert_to_customer")
    suspend fun convertToCustomer(
        @Field("authentication_token") authenticationToken: String,
        @Field("leadid") leadId: Int,
        @Field("firstname") firstname: String,
        @Field("lastname") lastname: String,
        @Field("title") title: String,
        @Field("email") email: String,
        @Field("company") company: String,
        @Field("phonenumber") phoneNumber: String,
        @Field("website") website: String,
        @Field("address") address: String,
        @Field("city") city: String,
        @Field("state") state: String,
        @Field("country") country: String,
        @Field("zip") zip: String,
        @Field("password") password: String,
        @Field("send_set_password_email") send_set_password_email: String,
        @Field("donotsendwelcomeemail") do_not_send_welcome_email: String,
        /*@Field("transfer_notes") transfer_notes: String,
        @Field("default_language") default_language: String,*/
        @Field("original_lead_email") original_lead_email: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_countries")
    suspend fun getCountryList(
        @Field("authentication_token") authenticationToken: String
    ): Response<CountryListResponse?>?

    @FormUrlEncoded
    @POST("add_lead")
    suspend fun addLead(
        @Field("authentication_token") authenticationToken: String,
        @Field("status") status: String?,
        @Field("source") source: String?,
        @Field("assigned") assigned: String?,
        @Field("name") name: String?,
        @Field("title") title: String?,
        @Field("email") email: String?,
        @Field("website") website: String?,
        @Field("phonenumber") phonenumber: String?,
        @Field("company") company: String?,
        @Field("address") address: String?,
        @Field("city") city: String?,
        @Field("state") state: String?,
        @Field("country") country: String?,
        @Field("zip") zip: String?,
        @Field("description") description: String?,
        @Field("is_public") is_public: Boolean,
        @Field("custom_contact_date") custom_contact_date: String?,
        @Field("contacted_today") contacted_today: String?,
        @Field("lead_value") lead_value: String?,
        @Field("custom_fields") customFields: String?
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_initial_lead_form")
    suspend fun getLeadInitialFormData(
        @Field("authentication_token") authenticationToken: String,
    ): Response<InitialLeadResponseModel?>?

    @FormUrlEncoded
    @POST("edit_my_lead")
    suspend fun updateLead(
        @Field("authentication_token") authenticationToken: String,
        @Field("status") status: String?,
        @Field("source") source: String?,
        @Field("assigned") assigned: String?,
        @Field("name") name: String?,
        @Field("title") title: String?,
        @Field("email") email: String?,
        @Field("website") website: String?,
        @Field("phonenumber") phonenumber: String?,
        @Field("company") company: String?,
        @Field("address") address: String?,
        @Field("city") city: String?,
        @Field("state") state: String?,
        @Field("country") country: String?,
        @Field("zip") zip: String?,
        @Field("description") description: String?,
        @Field("is_public") is_public: Boolean,
        @Field("lastcontact") lastcontact: String?,
        @Field("lead_value") lead_value: String?,
        @Field("leadid") leadid: String?,
        @Field("custom_fields") customFields: String?
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_initial_customer_form")
    suspend fun getCustomerInitialFormData(
        @Field("authentication_token") authenticationToken: String,
    ): Response<CustomersAddFormResponse?>?

    @FormUrlEncoded
    @POST("get_my_customers")
    suspend fun getCustomerList(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") startFrom: Int?,
        @Field("end_to") endTo: Int?,
    ): Response<CustomerListResponseModel?>?

    @FormUrlEncoded
    @POST("get_customer")
    suspend fun getCustomer(
        @Field("authentication_token") authenticationToken: String,
        @Field("group") group: String?,
        @Field("userId") userId: Int,
    ): Response<CustomerResponseModel?>?

    @FormUrlEncoded
    @POST("delete_my_customer")
    suspend fun deleteCustomer(
        @Field("authentication_token") authenticationToken: String,
        @Field("userId") userId: Int
    ): Response<CustomerListResponseModel?>?

    @FormUrlEncoded
    @POST("get_contacts")
    suspend fun getCustomerContacts(
        @Field("authentication_token") authenticationToken: String,
        @Field("userId") userId: Int,
    ): Response<CustomerContactsResponseModel?>?

    @FormUrlEncoded
    @POST("addContact")
    suspend fun addCustomerContact(
        @Field("authentication_token") authenticationToken: String,
        @Field("userId") userId: Int?,

        @Field("firstname") firstName: String,
        @Field("lastname") lastName: String,
        @Field("title") title: String?,
        @Field("email") email: String,
        @Field("phonenumber") phoneNumber: String?,
        @Field("direction") direction: String?,
        @Field("fakeusernameremembered") fakeUserNameRemembered: String?,
        @Field("fakepasswordremembered") fakePasswordRemembered: String?,
        @Field("password") password: String?,
        @Field("is_primary") isPrimary: String?,
        @Field("donotsendwelcomeemail") doNotSendWelcomeEmail: String?,
        @Field("send_set_password_email") sendSetPasswordEmail: String?,
        @Field("permissions[]") permissions: ArrayList<Int>?,
        @Field("invoice_emails") invoiceEmails: String?,
        @Field("estimate_emails") estimateEmails: String?,
        @Field("credit_note_emails") creditNoteEmails: String?,
        @Field("project_emails") projectEmails: String?,
        @Field("ticket_emails") ticketEmails: String?,
        @Field("task_emails") taskEmails: String?,
        @Field("contract_emails") contractEmails: String?
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("editContact")
    suspend fun editCustomerContact(
        @Field("authentication_token") authenticationToken: String,
        @Field("userId") userId: Int?,

        @Field("firstname") firstName: String,
        @Field("lastname") lastName: String,
        @Field("title") title: String?,
        @Field("email") email: String,
        @Field("phonenumber") phoneNumber: String?,
        @Field("direction") direction: String?,
        @Field("fakeusernameremembered") fakeUserNameRemembered: String?,
        @Field("fakepasswordremembered") fakePasswordRemembered: String?,
        @Field("password") password: String?,
        @Field("is_primary") isPrimary: String?,
        @Field("permissions[]") permissions: ArrayList<Int>?,
        @Field("invoice_emails") invoiceEmails: String?,
        @Field("estimate_emails") estimateEmails: String?,
        @Field("credit_note_emails") creditNoteEmails: String?,
        @Field("project_emails") projectEmails: String?,
        @Field("ticket_emails") ticketEmails: String?,
        @Field("task_emails") taskEmails: String?,
        @Field("contract_emails") contractEmails: String?
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("delete_contact")
    suspend fun deleteCustomerContact(
        @Field("authentication_token") authenticationToken: String,
        @Field("contactId") contactId: Int
    ): Response<CustomerListResponseModel?>?

    @FormUrlEncoded
    @POST("update_contact_active_status")
    suspend fun updateContactActiveStatus(
        @Field("authentication_token") authenticationToken: String,
        @Field("isActive") isActive: Int,
        @Field("userId") userId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("update_customer_active_status")
    suspend fun updateActiveStatus(
        @Field("authentication_token") authenticationToken: String,
        @Field("isActive") isActive: Int,
        @Field("userId") userId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("addCustomer")
    suspend fun addCustomer(
        @Field("authentication_token") authenticationToken: String,
        @Field("company") company: String,
        @Field("vat") vat: String,
        @Field("phonenumber") phonenumber: String?,
        @Field("website") website: String?,
        @Field("default_currency") default_currency: String?,
        @Field("default_language") default_language: String?,
        @Field("address") address: String?,
        @Field("city") city: String?,
        @Field("state") state: String?,
        @Field("zip") zip: String?,
        @Field("country") country: Int?,
        @Field("groups_in") groups_in: String?,
        @Field("billing_street") billing_street: String?,
        @Field("billing_city") billing_city: String?,
        @Field("billing_state") billing_state: String?,
        @Field("billing_zip") billing_zip: String?,
        @Field("billing_country") billing_country: Int?,
        @Field("shipping_street") shipping_street: String?,
        @Field("shipping_city") shipping_city: String?,
        @Field("shipping_state") shipping_state: String?,
        @Field("shipping_zip") shipping_zip: String?,
        @Field("shipping_country") shipping_country: Int?
    ): Response<CustomerAddedResponseModel?>?

    @FormUrlEncoded
    @POST("updateCustomer")
    suspend fun updateCustomer(
        @Field("authentication_token") authenticationToken: String,
        @Field("company") company: String,
        @Field("vat") vat: String,
        @Field("phonenumber") phonenumber: String?,
        @Field("website") website: String?,
        @Field("default_currency") default_currency: String?,
        @Field("default_language") default_language: String?,
        @Field("address") address: String?,
        @Field("city") city: String?,
        @Field("state") state: String?,
        @Field("zip") zip: String?,
        @Field("country") country: Int?,
        @Field("groups_in") groups_in: String?,
        @Field("billing_street") billing_street: String?,
        @Field("billing_city") billing_city: String?,
        @Field("billing_state") billing_state: String?,
        @Field("billing_zip") billing_zip: String?,
        @Field("billing_country") billing_country: Int?,
        @Field("shipping_street") shipping_street: String?,
        @Field("shipping_city") shipping_city: String?,
        @Field("shipping_state") shipping_state: String?,
        @Field("shipping_zip") shipping_zip: String?,
        @Field("shipping_country") shipping_country: Int?,
        @Field("clientId") clientId: Int?
    ): Response<CustomerAddedResponseModel?>?

    @FormUrlEncoded
    @POST("get_notes")
    suspend fun getNotes(
        @Field("authentication_token") authenticationToken: String,
        @Field("relId") relId: Int,
        @Field("relType") relType: String
    ): Response<CustomerNotesResponseModel?>?

    @FormUrlEncoded
    @POST("add_note")
    suspend fun addNote(
        @Field("authentication_token") authenticationToken: String,
        @Field("relId") relId: Int,
        @Field("relType") relType: String,
        @Field("description") description: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("update_note")
    suspend fun updateNote(
        @Field("authentication_token") authenticationToken: String,
        @Field("noteId") noteId: Int,
        @Field("description") description: String
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("delete_note")
    suspend fun deleteNote(
        @Field("authentication_token") authenticationToken: String,
        @Field("noteId") noteId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_tickets")
    suspend fun getTickets(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") start_from: Int,
        @Field("end_to") end_to: Int
    ): Response<SupportResponseModel?>?

    @FormUrlEncoded
    @POST("delete_ticket")
    suspend fun deleteTicket(
        @Field("authentication_token") authenticationToken: String,
        @Field("ticketId") ticketId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("load_initial_ticket_data")
    suspend fun getTicketInitialData(
        @Field("authentication_token") authenticationToken: String
    ): Response<InitialTicketResponseModel?>?

    @FormUrlEncoded
    @POST("add_ticket")
    suspend fun addTicket(
        @Field("authentication_token") authenticationToken: String,
        @Field("subject") subject: String,
        @Field("contactid") contactId: Int,
        @Field("name") name: String?,
        @Field("email") email: String?,
        @Field("department") departmentId: Int,
        @Field("cc") cc: String?,
        @Field("tags") tags: String?,
        @Field("assigned") assigned: Int?,
        @Field("priority") priority: Int,
        @Field("service") serviceId: Int?,
        @Field("project_id") projectId: Int?,
        @Field("message") message: String,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("update_ticket")
    suspend fun updateTicket(
        @Field("authentication_token") authenticationToken: String,
        @Field("ticketid") ticketId: Int,
        @Field("subject") subject: String,
        @Field("contactid") contactId: Int,
        @Field("name") name: String?,
        @Field("email") email: String?,
        @Field("department") departmentId: Int,
        @Field("cc") cc: String?,
        @Field("tags") tags: String?,
        @Field("assigned") assigned: Int?,
        @Field("priority") priority: Int,
        @Field("service") serviceId: Int?,
        @Field("project_id") projectId: Int?,
        @Field("message") message: String,
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_ticket_replies")
    suspend fun getTicketReplies(
        @Field("authentication_token") authenticationToken: String,
        @Field("ticketId") ticketId: Int
    ): Response<TicketRepliesResponseModel?>?

    @Multipart
    @POST("add_ticket_reply")
    suspend fun addTicketReply(
        @Part("staffid") staffId: RequestBody,
        @Part("ticketId") ticketId: RequestBody,
        @Part("message") message: RequestBody,
        @Part image: MultipartBody.Part?
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("dashboard_data")
    suspend fun getDashboardData(
        @Field("authentication_token") authenticationToken: String
    ): Response<DashboardDataResponseModel?>?

    @FormUrlEncoded
    @POST("notifications")
    suspend fun getNotifications(
        @Field("authentication_token") authenticationToken: String,
        @Field("start_from") start_from: Int,
        @Field("end_to") end_to: Int
    ): Response<NotificationsResponseModel?>?

    @FormUrlEncoded
    @POST("mark_notification_as_read")
    suspend fun markNotificationAsRead(
        @Field("authentication_token") authenticationToken: String,
        @Field("notificationId") notificationId: Int
    ): Response<CommonResponseModel?>?

    @FormUrlEncoded
    @POST("get_latest_notification")
    suspend fun getLatestNotification(
        @Field("authentication_token") authenticationToken: String
    ): Response<NotificationsResponseModel>?

    @FormUrlEncoded
    @POST("get_proposals")
    suspend fun getProposals(
        @Field("authentication_token") authenticationToken: String,
        @Field("rel_id") relId: Int?,
        @Field("rel_type") relType: String?
    ): Response<ProposalsResponseModel>?

    @FormUrlEncoded
    @POST("get_rel_items")
    suspend fun getRelItems(
        @Field("authentication_token") authenticationToken: String,
        @Field("relId") proposalId: Int?,
        @Field("relType") relType: String?
    ): Response<ProposalItemsResponseModel>?

    @FormUrlEncoded
    @POST("get_proposal_comments")
    suspend fun getProposalComments(
        @Field("authentication_token") authenticationToken: String,
        @Field("id") proposalId: Int
    ): Response<ProposalCommentsResponseModel>?

    @FormUrlEncoded
    @POST("get_proposal_initial_data")
    suspend fun getProposalInitialData(
        @Field("authentication_token") authenticationToken: String
    ): Response<InitialProposalResponseModel>?

    @FormUrlEncoded
    @POST("add_proposal")
    suspend fun addProposal(
        @Field("authentication_token") authenticationToken: String,
        @Field("subject") subject: String,
        @Field("rel_type") relType: String,
        @Field("rel_id") relId: Int,
        @Field("project_id") projectId: String?,
        @Field("date") date: String,
        @Field("open_till") openTill: String?,
        @Field("currency") currency: Int,
        @Field("discount_type") discountType: String?,
        @Field("tags") tags: String?,
        @Field("allow_comments") allowComments: String?,
        @Field("status") status: Int,
        @Field("assigned") assigned: Int,
        @Field("proposal_to") proposalTo: String?,
        @Field("address") address: String?,
        @Field("city") city: String,
        @Field("state") state: String?,
        @Field("country") country: Int,
        @Field("zip") zip: String?,
        @Field("email") email: String,
        @Field("phone") phone: String,
        @Field("item_select") itemSelected: String?,
        @Field("show_quantity_as") show_quantity_as: Int?,
        @Field("description") description: String?,
        @Field("long_description") longDescription: String?,
        @Field("quantity") quantity: Int,
        @Field("unit") unit: String?,
        @Field("rate") rate: Double?,
        @Field("taxname") taxName: String?,
        @Field("newitems") addedItems: String?,
        @Field("subtotal") subTotal: Double,
        @Field("discount_percent") discountPercent: Double,
        @Field("discount_total") discountTotal: Double?,
        @Field("adjustment") adjustment: Double?,
        @Field("total") total: Double,
        @Field("save_and_send") saveAndSend: Boolean?,
    ):  Response<CommonResponseModel>

    @FormUrlEncoded
    @POST("get_estimates")
    suspend fun getEstimates(
        @Field("authentication_token") authenticationToken: String,
        @Field("rel_id") relId: Int?,
        @Field("rel_type") relType: String?
    ): Response<EstimatesResponseModel>?

    @FormUrlEncoded
    @POST("view_task")
    suspend fun viewTask(
        @Field("authentication_token") authenticationToken: String,
        @Field("taskid") taskId: Int
    ): Response<ViewTaskResponseModel>?

    @FormUrlEncoded
    @POST("remove_attachment")
    suspend fun removeAttachment(
        @Field("authentication_token") authenticationToken: String,
        @Field("attachment_id") attachmentId: Int
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("update_task_followers")
    suspend fun updateTaskFollowers(
        @Field("authentication_token") authenticationToken: String,
        @Field("follower_ids") followerIds: String,
        @Field("task_id") taskId: String
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("update_task_assignees")
    suspend fun updateTaskAssignees(
        @Field("authentication_token") authenticationToken: String,
        @Field("assignee_ids") assigneeIds: String,
        @Field("task_id") taskId: String
    ): Response<CommonResponseModel>?

    @Multipart
    @POST("add_attachment")
    suspend fun addAttachment(
        @Part("staffid") staffId: RequestBody,
        @Part("task_id") taskId: RequestBody,
        @Part attachment: MultipartBody.Part?
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("remove_task_comment")
    suspend fun removeComment(
        @Field("authentication_token") authenticationToken: String,
        @Field("commentId") commentId: Int,
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("update_task_comment")
    suspend fun updateComment(
        @Field("authentication_token") authenticationToken: String,
        @Field("comment") comment: String,
        @Field("comment_id") commentId: String,
    ): Response<CommonResponseModel>?

    @Multipart
    @POST("add_task_comment")
    suspend fun addComment(
        @Part("staffid") staffId: RequestBody,
        @Part("task_id") taskId: RequestBody,
        @Part("comment") comment: RequestBody,
        @Part attachment: MultipartBody.Part?
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("remove_checklist_item")
    suspend fun removeChecklistItem(
        @Field("authentication_token") authenticationToken: String,
        @Field("checklist_item_id") checklistItemId: Int
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("toggle_task_checklist_item")
    suspend fun onChecklistItemToggled(
        @Field("authentication_token") authenticationToken: String,
        @Field("checklist_item_id") checklistItemId: Int,
        @Field("is_completed") is_completed: Int,
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("save_checklist_as_template")
    suspend fun saveChecklistAsTemplate(
        @Field("authentication_token") authenticationToken: String,
        @Field("description") description: String
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("add_checklist_item")
    suspend fun addCheckListItem(
        @Field("authentication_token") authenticationToken: String,
        @Field("description") description: String,
        @Field("task_id") task_id: String,
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("update_checklist_item")
    suspend fun updateCheckListItem(
        @Field("authentication_token") authenticationToken: String,
        @Field("description") description: String,
        @Field("checklist_item_id") checklistItemId: Int,
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("assign_staff_to_checklist_item")
    suspend fun assignStaffToChecklist(
        @Field("authentication_token") authenticationToken: String,
        @Field("assignedTo") assignedTo: Int,
        @Field("checklistItemId") checklistItemId: Int,
        @Field("task_id") taskId: Int,
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("get_invoices")
    suspend fun getInvoices(
        @Field("authentication_token") authenticationToken: String,
        @Field("rel_id") relId: Int?,
        @Field("rel_type") relType: String?
    ): Response<InvoiceResponse>?

    @FormUrlEncoded
    @POST("view_invoice")
    suspend fun viewInvoice(
        @Field("authentication_token") authenticationToken: String,
        @Field("invoiceId") invoiceId: Int?
    ): Response<ViewInvoiceResponseModel>?

    @FormUrlEncoded
    @POST("mark_invoice_as_sent")
    suspend fun markInvoiceAsSent(
        @Field("authentication_token") authenticationToken: String,
        @Field("invoiceId") invoiceId: Int?
    ): Response<CommonResponseModel>?

    @FormUrlEncoded
    @POST("get_invoices_initial_data")
    suspend fun getInitialInvoiceData(
        @Field("authentication_token") authenticationToken: String
    ): Response<InitialInvoiceResponseModel>?

    @FormUrlEncoded
    @POST("add_invoice")
    suspend fun addInvoice(
        @Field("authentication_token") authentication_token: String,
        @Field("cancel_merged_invoices") cancel_merged_invoices: String,
        @Field("clientid") clientid: Int,
        @Field("project_id") project_id: Int?,
        @Field("billing_street") billing_street: String?,
        @Field("billing_city") billing_city: String?,
        @Field("billing_state") billing_state: String?,
        @Field("billing_zip") billing_zip: String?,
        @Field("show_shipping_on_invoice") show_shipping_on_invoice: String?,
        @Field("shipping_street") shipping_street: String?,
        @Field("shipping_city") shipping_city: String?,
        @Field("shipping_state") shipping_state: String?,
        @Field("shipping_zip") shipping_zip: String?,
        @Field("number") number: String?,
        @Field("date") date: String,
        @Field("duedate") duedate: String?,
        @Field("tags") tags: String?,
        @Field("allowed_payment_modes") allowed_payment_modes: String,
        @Field("currency") currency: Int,
        @Field("sale_agent") sale_agent: Int?,
        @Field("recurring") recurring: Int,
        @Field("discount_type") discount_type: String?,
        @Field("repeat_every_custom") repeat_every_custom: Int?,
        @Field("repeat_type_custom") repeat_type_custom: String?,
        @Field("adminnote") adminnote: String?,
        @Field("item_select") item_select: String?,
        @Field("show_quantity_as") show_quantity_as: Int?,
        @Field("description") description: String?,
        @Field("long_description") long_description: String?,
        @Field("quantity") quantity: Int?,
        @Field("unit") unit: String?,
        @Field("rate") rate: Double?,
        @Field("newitems") newitems: String?,
        @Field("subtotal") subtotal: Double?,
        @Field("discount_percent") discount_percent: Double?,
        @Field("discount_total") discount_total: Double?,
        @Field("adjustment") adjustment: Double?,
        @Field("total") total: Double?,
        @Field("task_id") task_id: Int?,
        @Field("expense_id") expense_id: Int?,
        @Field("clientnote") clientnote: String?,
        @Field("terms") terms: String?,
        @Field("save_and_send") save_and_send: Boolean?
    ):  Response<CommonResponseModel>
}