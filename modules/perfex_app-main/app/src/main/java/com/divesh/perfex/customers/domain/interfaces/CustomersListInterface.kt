package com.divesh.perfex.customers.domain.interfaces

import com.divesh.perfex.customers.domain.models.CustomerListResponseModel

interface CustomersListInterface {
    fun deleteCustomer(customer: CustomerListResponseModel.Customer, position: Int)
    fun editCustomer(customer: CustomerListResponseModel.Customer)
    fun sendMail(email: String?)
    fun activeStatusChanged(isChecked: Boolean, customerId: Int)
    fun dialCall(phoneNumber: String?)
    fun viewCustomer(customer: CustomerListResponseModel.Customer)
}