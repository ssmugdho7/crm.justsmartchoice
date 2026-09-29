package com.divesh.perfex.leads.domain.models

data class ViewLeadResponseModel(
    val lead: Lead?,
    val message: String?,
    val status: Int?
) {
    data class Lead(
        val addedfrom: Int?,
        val address: String?,
        val assigned: String?,
        val city: String?,
        val client_id: Int?,
        val company: String?,
        val country: Int?,
        val countryName: String?,
        val custom_fields: List<CustomField?>?,
        val custom_fields_values: List<CustomFieldsValue?>?,
        val date_converted: String?,
        val dateadded: String?,
        val dateassigned: String?,
        val default_language: String?,
        val description: String?,
        val email: String?,
        val email_integration_uid: String?,
        val from_form_id: Int?,
        val hash: String?,
        val id: Int?,
        val is_imported_from_email_integration: Int?,
        val is_public: Int?,
        val junk: Int?,
        val last_lead_status: Int?,
        val last_status_change: String?,
        val lastcontact: String?,
        val lead_value: String?,
        val leadorder: Int?,
        val lost: Int?,
        val name: String?,
        val phonenumber: String?,
        val source: String?,
        val state: String?,
        val status: String?,
        val title: String?,
        val website: String?,
        val zip: Any?
    ) {
        data class CustomField(
            val active: Int?,
            val bs_column: Int?,
            val default_value: String?,
            val disalow_client_to_edit: Int?,
            val display_inline: Int?,
            val field_order: Int?,
            val fieldto: String?,
            val id: Int?,
            val name: String?,
            val only_admin: Int?,
            val options: String?,
            val required: Int?,
            val show_on_client_portal: Int?,
            val show_on_pdf: Int?,
            val show_on_table: Int?,
            val show_on_ticket_form: Int?,
            val slug: String?,
            val type: String?
        )

        data class CustomFieldsValue(
            val fieldid: Int?,
            val fieldto: String?,
            val id: Int?,
            val relid: Int?,
            val value: String?
        )
    }
}