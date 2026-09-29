package com.divesh.perfex.sales.domain.models.proposals


import kotlinx.parcelize.Parcelize
import android.os.Parcelable

@Parcelize
data class ProposalItemsResponseModel(
    var items: List<Item> = listOf(),
    var taxes: List<Taxes> = listOf(),
    var message: String? = "", // Proposal items loaded
    var status: Int = 0 // 1
) : Parcelable {
    @Parcelize
    data class Item(
        var description: String? = "", // Test Description
        var id: Int = 0, // 1
        var item_order: Int = 0, // 1
        var long_description: String? = "", // Test long Description
        var qty: Int = 0, // 2
        var rate: Int = 0, // 100
        var rel_id: Int = 0, // 1
        var rel_type: String? = "", // proposal
        var unit: String? = "" // pcs
    ) : Parcelable

    @Parcelize
    data class Taxes(
        val id: Int = 0,
        val itemid: Int = 0,
        val rel_id: Int = 0,
        val rel_type: String? = "",
        val taxrate: Float = 0F,
        val taxname: String? = "",
    ): Parcelable
}