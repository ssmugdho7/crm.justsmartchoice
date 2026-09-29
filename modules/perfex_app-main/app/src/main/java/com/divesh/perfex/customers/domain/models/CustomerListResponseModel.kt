package com.divesh.perfex.customers.domain.models

import java.io.Serializable

data class CustomerListResponseModel(
    val customers: List<Customer> = listOf(),
    val message: String? = "", // Customers found
    val status: Int = 0 // 1
) {
    data class Customer(
        val company: String? = "", // divesh111
        val contact_id: Int? = 0, // 1
        val customerGroups: String? = "", // Demo1,Demo2
        val datecreated: String? = "", // 2022-07-27 12:17:47
        val email: String? = "", // di@gmail.com
        val firstname: String? = "", // Test
        val lastname: String? = "", // Compan
        val phonenumber: String? = "", // 7894561320
        val registration_confirmed: Int = 0, // 1
        val tblclients_active: Int = 0, // 1
        val userid: Int = 0, // 7
        val zip: String? = "" // 302002
    ): Serializable
}