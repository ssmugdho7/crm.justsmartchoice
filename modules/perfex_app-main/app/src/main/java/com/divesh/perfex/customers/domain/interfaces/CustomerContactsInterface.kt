package com.divesh.perfex.customers.domain.interfaces

import com.divesh.perfex.customers.domain.models.CustomerResponseModel

interface CustomerContactsInterface {
    fun deleteContact(id: Int, position: Int)
    fun editContact(record: CustomerResponseModel.Data.Contact)
    fun activeStatusChanged(isChecked: Boolean, contactId: Int)
    fun sendMail(email: String?)
    fun dialCall(phoneNumber: String?)
}