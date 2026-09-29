package com.divesh.perfex.sales.domain.models.proposals


import kotlinx.parcelize.Parcelize
import android.os.Parcelable

@Parcelize
data class ProposalsResponseModel(
    var message: String? = "", // Proposals loaded
    var proposals: List<Proposal> = listOf(),
    var status: Int = 0 // 1
) : Parcelable {
    @Parcelize
    data class Proposal(
        var acceptance_date: String? = null, // null
        var acceptance_email: String? = null, // null
        var acceptance_firstname: String? = null, // null
        var acceptance_ip: String? = null, // null
        var acceptance_lastname: String? = null, // null
        var addedfrom: Int = 0, // 1
        var address: String? = "", //
        var adjustment: Int = 0, // 50
        var allow_comments: Int = 0, // 1
        var assigned: Int = 0, // 0
        var city: String? = "", // Jaipur
        var content: String? = "", // {proposal_items}
        var country: Int = 0, // 102
        var currency: Int = 0, // 1
        var currency_name: String? = "", // USD
        var currencyid: Int = 0, // 1
        var date: String? = "", // 2022-12-15
        var date_converted: String? = null, // null
        var datecreated: String? = "", // 2022-12-15 22:58:48
        var decimal_separator: String? = "", // .
        var discount_percent: Int = 0, // 10
        var discount_total: Int = 0, // 0
        var discount_type: String? = "",
        var email: String? = "", // diveshahuja8@gmail.com11
        var estimate_id: String? = null, // null
        var hash: String? = "", // 6aa082e3d013ae4c872e81b4f428247d
        var id: Int = 0, // 1
        var invoice_id: String? = null, // null
        var is_expiry_notified: Int = 0, // 0
        var isdefault: Int = 0, // 1
        var name: String? = "", // USD
        var open_till: String? = "", // 2022-12-22
        var phone: String? = "", // 917894651320
        var pipeline_order: Int = 0, // 1
        var placement: String? = "", // before
        var project_id: String? = null, // null
        var proposal_to: String? = "", // Test compString
        var rel_id: Int = 0, // 2
        var rel_type: String? = "", // lead
        var short_link: String? = null, // null
        var show_quantity_as: Int = 0, // 1
        var signature: String? = null, // null
        var state: String? = "", // Rajasthan
        var status: Int = 0, // 6
        var subject: String? = "", // Test Subject
        var subtotal: Double? = 0.00, // 200
        var symbol: String? = "", // $
        var thousand_separator: String? = "", // ,
        var total: Double? = 0.00, // 250
        var total_tax: Double? = 0.00, // 0
        var zip: Int? = 0 // 302017
    ) : Parcelable
}