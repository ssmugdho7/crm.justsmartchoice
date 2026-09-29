package com.divesh.perfex.customers.domain.models

data class CustomerNotesResponseModel (
    val notes: List<Note> = listOf(),
    val message: String? = "", // Customers found
    val status: Int = 0 // 1
){
    data class Note(
        val id: Int = 0,
        val rel_id: Int = 0,
        val rel_type: String? = "",
        val description: String? = "",
        val date_contacted: String? = "",
        val addedfrom: Int = 0,
        val dateadded: String? = ""
    ):java.io.Serializable
}