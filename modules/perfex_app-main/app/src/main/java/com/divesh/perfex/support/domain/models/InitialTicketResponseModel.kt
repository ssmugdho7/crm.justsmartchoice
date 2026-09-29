package com.divesh.perfex.support.domain.models

import com.divesh.perfex.login.domain.models.Staff

data class InitialTicketResponseModel(
    val contacts: List<Contact>,
    val departments: List<Department>,
    val knowledgeBase: List<KnowledgeBase>?,
    val preDefinedReplies: List<PreDefinedReply>,
    val services: List<Service>?,
    val staff: List<Staff>,
    val status: Int,
    val message: String
){
    data class Contact(
        val active: Int,
        val contract_emails: Int,
        val credit_note_emails: Int,
        val datecreated: String,
        val direction: String,
        val email: String,
        val email_verification_key: String?,
        val email_verification_sent_at: String?,
        val email_verified_at: String,
        val estimate_emails: Int,
        val firstname: String,
        val id: Int,
        val invoice_emails: Int,
        val is_primary: Int,
        val last_ip: String?,
        val last_login: String?,
        val last_password_change: String,
        val lastname: String,
        val new_pass_key: String?,
        val new_pass_key_requested: String?,
        val password: String,
        val phonenumber: String?,
        val profile_image: String?,
        val project_emails: Int,
        val task_emails: Int,
        val ticket_emails: Int,
        val title: String,
        val userid: Int
    )
    data class Department(
        val calendar_id: String?,
        val delete_after_import: Int,
        val departmentid: Int,
        val email: String,
        val email_from_header: Int,
        val encryption: String,
        val folder: String,
        val hidefromclient: Int,
        val host: String,
        val imap_username: String,
        val name: String,
        val password: String
    )
    data class KnowledgeBase(
        val active: Int,
        val article_order: Int,
        val articlegroup: Int,
        val articleid: Int,
        val color: String,
        val datecreated: String,
        val description: String,
        val group_order: Int,
        val group_slug: String,
        val groupid: Int,
        val name: String,
        val slug: String,
        val staff_article: Int,
        val subject: String
    )
    data class PreDefinedReply(
        val id: Int,
        val message: String,
        val name: String
    )
    data class Service(
        val name: String,
        val serviceid: Int
    )
}