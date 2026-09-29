package com.divesh.perfex.sales.domain.models.invoices

import android.os.Parcelable
import com.divesh.perfex.customers.domain.models.CustomerListResponseModel
import com.divesh.perfex.login.domain.models.Staff
import com.divesh.perfex.sales.domain.models.Country
import com.divesh.perfex.sales.domain.models.item.Currency
import com.divesh.perfex.sales.domain.models.item.Item
import com.divesh.perfex.sales.domain.models.item.Tax
import kotlinx.parcelize.Parcelize

@Parcelize
data class InitialInvoiceResponseModel(
    val currencies: List<Currency?>?,
    val countries: List<Country?>?,
    val customers: List<CustomerListResponseModel.Customer?>?,
    val item_data: ItemData?,
    val message: String?,
    val payment_modes: List<PaymentMode?>?,
    val staff_list: List<Staff?>?,
    val status: Int?
) : Parcelable {
    @Parcelize
    data class ItemData(
        val items: List<Item>,
        val taxes: List<Tax>,
    ) : Parcelable

    @Parcelize
    data class PaymentMode(
        val active: Int?,
        val description: String?,
        val expenses_only: Int?,
        val id: String?,
        val invoices_only: Int?,
        val name: String,
        val selected_by_default: Int?,
        val show_on_pdf: Int?
    ): Parcelable
}