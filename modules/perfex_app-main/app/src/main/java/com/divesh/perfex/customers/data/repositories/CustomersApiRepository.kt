package com.divesh.perfex.customers.data.repositories

import com.divesh.perfex.core.api.ApiInterface

class CustomersApiRepository(private val apiService: ApiInterface) {
    suspend fun addCustomer(
       staffId: String,
        company: String,
        vat: String,
        phonenumber: String?,
        website: String?,
        groups_in: String?,
        address: String?,
        city: String?,
        state: String?,
        zip: String?,
        country: Int?,
        billing_street: String?,
        billing_city: String?,
        billing_state: String?,
        billing_zip: String?,
        billing_country: Int?,
        shipping_street: String?,
        shipping_city: String?,
        shipping_state: String?,
        shipping_zip: String?,
        shipping_country: Int?
    ) = apiService.addCustomer(
        staffId,
        company,
        vat,
        phonenumber,
        website,
        "",
        "",
        address,
        city,
        state,
        zip,
        country,
        groups_in,
        billing_street,
        billing_city,
        billing_state,
        billing_zip,
        billing_country,
        shipping_street,
        shipping_city,
        shipping_state,
        shipping_zip,
        shipping_country
    )
    suspend fun updateCustomer(
       staffId: String,
        company: String,
        vat: String,
        phonenumber: String?,
        website: String?,
        groups_in: String?,
        address: String?,
        city: String?,
        state: String?,
        zip: String?,
        country: Int?,
        billing_street: String?,
        billing_city: String?,
        billing_state: String?,
        billing_zip: String?,
        billing_country: Int?,
        shipping_street: String?,
        shipping_city: String?,
        shipping_state: String?,
        shipping_zip: String?,
        shipping_country: Int?,
        clientId: Int?
    ) = apiService.updateCustomer(
        staffId,
        company,
        vat,
        phonenumber,
        website,
        "",
        "",
        address,
        city,
        state,
        zip,
        country,
        groups_in,
        billing_street,
        billing_city,
        billing_state,
        billing_zip,
        billing_country,
        shipping_street,
        shipping_city,
        shipping_state,
        shipping_zip,
        shipping_country,
        clientId
    )
    suspend fun getCustomerList(staffId: String, startFrom: Int, limit: Int) = apiService.getCustomerList(staffId, startFrom, limit)
    suspend fun getCustomer(staffId: String, group: String, userId: Int) = apiService.getCustomer(staffId, group, userId)
    suspend fun deleteCustomer(staffId: String, customerId: Int) = apiService.deleteCustomer(staffId, customerId)
    suspend fun updateActiveStatus(staffId: String, isActive: Int, customerId: Int) = apiService.updateActiveStatus(staffId, isActive, customerId)
    suspend fun getCustomerInitialFormData(staffId: String)= apiService.getCustomerInitialFormData(staffId)

    //contacts
    suspend fun getCustomerContacts(staffId: String, userId: Int) = apiService.getCustomerContacts(staffId, userId)
    suspend fun addCustomerContact(staffId: String, userId: Int?, firstName: String,lastName: String,title: String?,email: String,phoneNumber: String?,direction: String?,fakeUserNameRemembered: String?,fakePasswordRemembered: String?,password: String?,is_primary: String?,doNotSendWelcomeEmail: String?,sendSetPasswordEmail: String?,permissions: ArrayList<Int>?,invoiceEmails: String?,estimateEmails: String?,creditNoteEmails: String?,projectEmails: String?,ticketEmails: String?,taskEmails: String?,contractEmails: String?) = apiService.addCustomerContact(staffId, userId, firstName,lastName,title,email,phoneNumber,direction,fakeUserNameRemembered,fakePasswordRemembered,password,is_primary,doNotSendWelcomeEmail,sendSetPasswordEmail,permissions,invoiceEmails,estimateEmails,creditNoteEmails,projectEmails,ticketEmails,taskEmails,contractEmails)
    suspend fun editCustomerContact(staffId: String, userId: Int?, firstName: String,lastName: String,title: String?,email: String,phoneNumber: String?,direction: String?,fakeUserNameRemembered: String?,fakePasswordRemembered: String?,password: String?,is_primary: String?,permissions: ArrayList<Int>?,invoiceEmails: String?,estimateEmails: String?,creditNoteEmails: String?,projectEmails: String?,ticketEmails: String?,taskEmails: String?,contractEmails: String?) = apiService.editCustomerContact(staffId, userId, firstName,lastName,title,email,phoneNumber,direction,fakeUserNameRemembered,fakePasswordRemembered,password,is_primary,permissions,invoiceEmails,estimateEmails,creditNoteEmails,projectEmails,ticketEmails,taskEmails,contractEmails)
    suspend fun updateContactActiveStatus(staffId: String, isActive: Int, contactId: Int) = apiService.updateContactActiveStatus(staffId, isActive, contactId)
    suspend fun deleteCustomerContact(staffId: String, contactId: Int) = apiService.deleteCustomerContact(staffId, contactId)

    //notes
    suspend fun getNotes(staffId: String, relId: Int, relType: String) = apiService.getNotes(staffId, relId, relType)
    suspend fun addNote(staffId: String, relId: Int, relType: String, description: String) = apiService.addNote(staffId, relId, relType, description)
    suspend fun updateNote(staffId: String, noteId: Int, description: String) = apiService.updateNote(staffId, noteId, description)
    suspend fun deleteNote(staffId: String, noteId: Int) = apiService.deleteNote(staffId, noteId)
}