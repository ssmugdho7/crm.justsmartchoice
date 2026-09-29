package com.divesh.perfex.sales.domain.models.invoices

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class InvoiceResponse(
    val invoices: List<Invoice> = listOf(),
    val message: String = "", // Invoices loaded
    val status: Int = 0 // 1
) : Parcelable {
    @Parcelize
    data class Invoice(
        val addedfrom: Int = 0, // 1
        val adjustment: Int = 0, // 0
        val adminnote: String? = "", // null
        val allowed_payment_modes: String = "", // a:1:{i:0;s:4:"cash";}
        val billing_city: String? = "", // null
        val billing_country: Int = 0, // 0
        val billing_state: String? = "", // null
        val billing_street: String? = "", // null
        val billing_zip: String? = "", // null
        val cancel_overdue_reminders: Int = 0, // 0
        val clientid: Int = 0, // 3
        val clientnote: String? = "", // null
        val currency: Int = 0, // 3
        val currency_name: String = "", // MYR
        val currencyid: Int = 0, // 3
        val custom_recurring: Int = 0, // 0
        val cycles: Int = 0, // 0
        val date: String = "", // 2021-01-16
        val datecreated: String = "", // 2021-01-16 14:03:06
        val datesend: String? = "", // null
        val decimal_separator: String = "", // .
        val deleted_customer_name: String? = "", // null
        val discount_percent: Int = 0, // 0
        val discount_total: Int = 0, // 0
        val discount_type: String? = "", // null
        val duedate: String? = "", // 2021-01-16
        val hash: String = "", // 5c76d3e8ae7cffdf752ab3cbc006ff8c
        val id: Int = 0, // 1
        val include_shipping: Int = 0, // 1
        val is_recurring_from: String? = "", // null
        val isdefault: Int = 0, // 1
        val last_due_reminder: String? = "", // null
        val last_overdue_reminder: String? = "", // null
        val last_recurring_date: String? = "", // null
        val name: String = "", // MYR
        val number: Int = 0, // 1
        val number_format: Int = 0, // 1
        val placement: String = "", // before
        val prefix: String = "", // INV-
        val project_id: Int = 0, // 0
        val recurring: Int = 0, // 0
        val recurring_type: String? = "", // null
        val sale_agent: Int = 0, // 0
        val sent: Int = 0, // 0
        val shipping_city: String? = "", // null
        val shipping_country: String? = "", // null
        val shipping_state: String? = "", // null
        val shipping_street: String? = "", // null
        val shipping_zip: String? = "", // null
        val short_link: String? = "", // null
        val show_quantity_as: Int = 0, // 1
        val show_shipping_on_invoice: Int = 0, // 1
        val status: Int = 0, // 2
        val subscription_id: Int = 0, // 0
        val subtotal: Int = 0, // 1499
        val symbol: String = "", // RM
        val terms: String? = "", // null
        val thousand_separator: String = "", // ,
        val token: String? = "", // null
        val total: Int = 0, // 1499
        val total_cycles: Int = 0, // 0
        val invoiceNumber: String? = "", // 0
        val fancyTax: String? = "",
        val fancyTotal: String? = "",
        val fancyDueDate: String? = "",
        val companyName: String? = ""
    ) : Parcelable
}