package com.divesh.perfex.customers.domain.models

data class CustomerContactsResponseModel(
    val message: String? = "", // Customer added successfully.
    val status: Int? = 0, // 1
    val contacts: List<CustomerResponseModel.Data.Contact>
)
