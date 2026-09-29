package com.divesh.perfex.sales.domain.models.proposals


import kotlinx.parcelize.Parcelize
import android.os.Parcelable
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import com.divesh.perfex.leads.domain.models.Lead
import com.divesh.perfex.login.domain.models.Staff
import com.divesh.perfex.sales.domain.models.Country
import com.divesh.perfex.sales.domain.models.item.Currency
import com.divesh.perfex.sales.domain.models.item.Item
import com.divesh.perfex.sales.domain.models.item.Tax

@Parcelize
data class InitialProposalResponseModel(
    var currencies: List<Currency?>? = null,
    var customers: List<CustomerResponseModel.Data.Client?>? = null,
    var leads: List<Lead?>? = null,
    var staff_list: List<Staff?>? = null,
    var countries: List<Country?>? = null,
    var itemData: ItemData? = null,
    var message: String? = null, // Proposals data loaded
    var status: Int? = null // 1
) : Parcelable {
    @Parcelize
    data class ItemData(
        val items: List<Item>,
        val taxes: List<Tax>,
    ) : Parcelable
}