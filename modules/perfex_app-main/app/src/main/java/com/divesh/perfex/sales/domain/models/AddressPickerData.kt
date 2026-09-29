package com.divesh.perfex.sales.domain.models

data class AddressPickerData(
    var streetBilling: String?,
    var cityBilling: String?,
    var stateBilling: String?,
    var zipBilling: String?,
    var countryBilling: Country?,
    var allowShipping: Boolean?,
    var showShippingDetailsOnInvoice: Boolean?,
    var streetShipping: String?,
    var cityShipping: String?,
    var stateShipping: String?,
    var zipShipping: String?,
    var countryShipping: Country?,
)