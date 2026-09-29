package com.divesh.perfex.sales.domain.models.proposals

import com.divesh.perfex.sales.domain.models.item.Tax

data class SelectedItem(
    val id: Int = 0,
    val description: String? = "",
    val long_description: String? = "",
    val unit: String? = "",
    var rate: Double = 0.00,
    var qty: Int = 0,
    var taxes: List<Tax?>
)