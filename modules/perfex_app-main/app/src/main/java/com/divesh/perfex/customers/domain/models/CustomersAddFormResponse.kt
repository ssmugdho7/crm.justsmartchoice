package com.divesh.perfex.customers.domain.models

import com.divesh.perfex.leads.domain.models.CountryListResponse

data class CustomersAddFormResponse(
    val message: String? = "",
    val status: Int = 0,
    val defaultCountry: Int? = 0,
    val countries: List<CountryListResponse.Country> = listOf(),
    val groups: List<CustomersGroups> = listOf()
) {
    data class CustomersGroups(
        val id: Int = 0,
        val name: String? = ""
    )
}