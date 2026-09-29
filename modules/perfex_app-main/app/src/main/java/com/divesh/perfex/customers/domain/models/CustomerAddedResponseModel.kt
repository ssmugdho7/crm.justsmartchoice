package com.divesh.perfex.customers.domain.models

import java.io.Serializable

data class CustomerAddedResponseModel(
    val customer: Customer = Customer(),
    val id: Int = 0, // 7
    val message: String? = "", // Customer added successfully.
    val status: Int? = 0 // 1
) {
    data class Customer(
        val active: Int? = 0, // 1
        val addedfrom: Int? = 0, // 0
        val address: String? = "",
        val billing_city: String? = "",
        val billing_country: Int? = 0, // 0
        val billing_state: String? = "",
        val billing_street: String? = "",
        val billing_zip: String? = "",
        val city: String? = "",
        val company: String? = "", // dibesh
        val country: Int? = 0, // 0
        val datecreated: String? = "", // 2022-07-27 12:17:47
        val default_currency: Int? = 0, // 0
        val default_language: String? = "",
        val latitude: String? = "", // null
        val leadid: String? = "", // null
        val longitude: String? = "", // null
        val phonenumber: String? = "",
        val registration_confirmed: Int? = 0, // 1
        val shipping_city: String? = "",
        val shipping_country: Int? = 0, // 0
        val shipping_state: String? = "",
        val shipping_street: String? = "",
        val shipping_zip: String? = "",
        val show_primary_contact: Int? = 0, // 0
        val state: String? = "",
        val stripe_id: String? = "", // null
        val userid: Int? = 0, // 7
        val vat: String? = "",
        val website: String? = "",
        val zip: String? = ""
    ): Serializable
}