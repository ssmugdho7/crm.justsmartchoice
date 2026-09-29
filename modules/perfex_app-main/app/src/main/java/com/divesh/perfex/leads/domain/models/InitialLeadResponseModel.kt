package com.divesh.perfex.leads.domain.models

data class InitialLeadResponseModel(
    val status: Int = 0,
    val message: String? = "",
    val leadStatus: List<LeadsStatus.Status>,
    val source: List<LeadsStatus.Status>,
    val countries: List<CountryListResponse.Country>,
    val assignees: List<AssigneeList.Assignee>,
    val customFields: List<CustomField>,
    val defaultSelectedFields: DefaultSelectedFields?,
){

    data class DefaultSelectedFields(
        val leads_default_source: Int? = 0,
        val leads_default_status: Int? = 0,
        val leads_default_country: Int? = 0
    )
}
