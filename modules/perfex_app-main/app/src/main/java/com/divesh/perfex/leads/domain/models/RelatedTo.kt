package com.divesh.perfex.leads.domain.models

data class RelatedTo(
    val customers: List<Customer> = listOf(),
    val leads: List<Lead> = listOf(),
    val message: String? = "", // Data found
    val status: Int = 0 // 1
) {
    data class Customer(
        val company: String? = "", // test
        val phonenumber: String? = "", //
        val userid: Int = 0 // 9
    )

    data class Lead(
        val company: String? = "", // Test company
        val id: Int = 0, // 9
        val name: String? = "", // Test
        val title: String? = "" // Manager
    )

    data class Currency(
        val id: Int? = 0,
        val symbol: String? = "",
        val name: String? = "",
        val decimal_separator: String? = "",
        val thousand_separator: String? = "",
        val placement: String? = "",
        val is_default: Int? = 0,
    )
}