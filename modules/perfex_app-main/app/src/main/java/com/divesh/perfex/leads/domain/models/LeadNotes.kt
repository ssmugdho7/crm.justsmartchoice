package com.divesh.perfex.leads.domain.models

data class LeadNotes(
    val notes: List<Note> = listOf(),
    val status: Int = 0
){
    data class Note(
        val addedfrom: Int = 0,
        val date_contacted: String? = "",
        val dateadded: String? = "",
        val description: String? = "",
        val firstname: String? = "",
        val id: Int = 0,
        val lastname: String? = "",
        val rel_id: Int = 0,
        val rel_type: String? = ""
    )
}
