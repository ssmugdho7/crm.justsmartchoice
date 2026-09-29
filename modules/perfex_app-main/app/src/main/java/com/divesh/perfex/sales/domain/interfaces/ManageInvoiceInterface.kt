package com.divesh.perfex.sales.domain.interfaces

import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse

interface ManageInvoiceInterface {
    fun onInvoiceViewed(invoice: InvoiceResponse.Invoice)
    fun onInvoiceDetailsBtnClicked(invoice: InvoiceResponse.Invoice)
    fun onInvoiceEdit(invoice: InvoiceResponse.Invoice)
}