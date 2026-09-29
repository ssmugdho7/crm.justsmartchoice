package com.divesh.perfex.core.helpers.dynamic_form.models

data class DynamicFormValues(
    val custom_fields: List<CustomFieldValue?>?
) {
    data class CustomFieldValue(
        val key: Int?,
        val value: Any?
    )
}
