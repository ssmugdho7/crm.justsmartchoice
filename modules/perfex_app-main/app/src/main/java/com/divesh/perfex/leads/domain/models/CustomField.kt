package com.divesh.perfex.leads.domain.models

data class CustomField(
    val id: Int = 0,
    val fieldto: String? = "",
    val name: String? = "",
    val slug: String? = "",
    val required: Int = 0,
    val type: String? = "",
    val options: String? = "",
    val display_inline: Int = 0,
    val field_order: Int = 0,
    val active: Int = 0,
    val show_on_pdf: Int = 0,
    val show_on_ticket_form: Int = 0,
    val only_admin: Int = 0,
    val show_on_table: Int = 0,
    val show_on_client_portal: Int = 0,
    val disalow_client_to_edit: Int = 0,
    val bs_column: Int = 0,
    val default_value: String? = "",
)
