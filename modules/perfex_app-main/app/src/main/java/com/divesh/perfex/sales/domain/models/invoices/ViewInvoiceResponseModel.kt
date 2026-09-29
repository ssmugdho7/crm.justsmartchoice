package com.divesh.perfex.sales.domain.models.invoices

data class ViewInvoiceResponseModel(
    val data: Data = Data(),
    val message: String = "", // Invoice loaded
    val status: Int = 0 // 1
) {
    data class Data(
        val activity: List<Activity> = listOf(),
        val applied_credits: List<Any> = listOf(),
        val invoice: Invoice = Invoice(),
        val companyInformation: CompanyInformation = CompanyInformation(),
        val invoice_recurring_invoices: List<Any> = listOf(),
        val invoices_to_merge: List<Any> = listOf(),
        val members: List<Member> = listOf(),
        val payments: List<Payment> = listOf(),
        val record_payment: Boolean = false, // false
        val send_later: Boolean = false, // false
        val template: Template = Template(),
        val template_disabled: Boolean = false, // true
        val template_id: Int = 0, // 2
        val template_name: String = "", // invoice-send-to-client
        val template_system_name: String = "", // Send Invoice to Customer
        val totalNotes: Int = 0, // 0
        val total_paid: String? = "", // 0
        val amount_due: String? = "" // 0
    ) {
        data class Activity(
            val additional_data: String? = "", // a:2:{i:0;s:36:"<original_status>1</original_status>";i:1;s:26:"<new_status>2</new_status>";}
            val date: String = "", // 2021-01-16 14:03:06
            val description: String = "", // invoice_activity_created
            val full_name: String = "", // hubert chong
            val id: Int = 0, // 1
            val rel_id: Int = 0, // 1
            val rel_type: String = "", // invoice
            val staffid: Int = 0 // 1
        )

        data class Invoice(
            val addedfrom: Int = 0, // 1
            val adjustment: Int = 0, // 0
            val adminnote: String? = "", // null
            val allowed_payment_modes: String = "", // a:1:{i:0;s:4:"cash";}
            val attachments: List<Any> = listOf(),
            val billing_city: String? = "",
            val billing_country: Int = 0, // 0
            val billing_state: String? = "",
            val billing_street: String? = "",
            val billing_zip: String? = "",
            val cancel_overdue_reminders: Int = 0, // 0
            val client: Client = Client(),
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
            val decimal_separator: String? = "", // .
            val deleted_customer_name: String? = "", // null
            val discount_percent: Int = 0, // 0
            val discount_total: Int = 0, // 0
            val discount_type: String? = "",
            val duedate: String? = "", // 2021-01-16
            val hash: String = "", // 5c76d3e8ae7cffdf752ab3cbc006ff8c
            val id: Int = 0, // 1
            val include_shipping: Int = 0, // 1
            val is_recurring_from: String? = "", // null
            val isdefault: Int = 0, // 1
            val items: List<Item> = listOf(),
            val last_due_reminder: String? = "", // null
            val last_overdue_reminder: String? = "", // null
            val last_recurring_date: String? = "", // null
            val name: String = "", // MYR
            val number: Int = 0, // 1
            val number_format: Int = 0, // 1
            val payments: List<Payment> = listOf(),
            val placement: String = "", // before
            val prefix: String = "", // INV-
            val project_id: Int = 0, // 0
            val recurring: Int = 0, // 0
            val recurring_type: String? = "", // null
            val sale_agent: Int = 0, // 0
            val scheduled_email: String? = "", // null
            val sent: Int = 0, // 0
            val shipping_city: String = "",
            val shipping_country: String? = "", // null
            val shipping_state: String = "",
            val shipping_street: String? = "",
            val shipping_zip: String? = "",
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
            val total_left_to_pay: Int = 0, // 0
            val total_tax: Int = 0, // 0
            val visible_attachments_to_customer_found: Boolean = false // false
        ) {
            data class Client(
                val active: Int = 0, // 1
                val addedfrom: Int = 0, // 1
                val address: String = "",
                val billing_city: String = "",
                val billing_country: Int = 0, // 0
                val billing_state: String = "",
                val billing_street: String = "",
                val billing_zip: String = "",
                val city: String = "",
                val company: String = "", // Everest demo
                val country: Int = 0, // 133
                val datecreated: String = "", // 2020-12-29 00:12:31
                val default_currency: Int = 0, // 0
                val default_language: String = "",
                val latitude: String? = "", // null
                val leadid: String? = "", // null
                val longitude: String? = "", // null
                val phonenumber: Long = 0, // 60168774145
                val registration_confirmed: Int = 0, // 1
                val shipping_city: String = "",
                val shipping_country: Int = 0, // 0
                val shipping_state: String = "",
                val shipping_street: String = "",
                val shipping_zip: String = "",
                val show_primary_contact: Int = 0, // 0
                val state: String = "",
                val stripe_id: String? = "", // null
                val userid: Int = 0, // 3
                val vat: String? = "", // null
                val website: String = "",
                val zip: String = ""
            )

            data class Item(
                val description: String = "", // SQL POS
                val id: Int = 0, // 1
                val item_order: Int = 0, // 0
                val long_description: String = "", // A POINT OF SALES system just work like Plug&Play with SQL Financial Accounting System.<br />GST Ready.<br />Sql-pos are able to read SQL item code and post to SQL as Cash Sales.<br />Quantity Discount , Category Discount, Stock Group Discount.<br />With Touch Feature, Photo Button.<br />Import master list from Excel File into Sql Accounting, (item code,Customer & supplier).<br />There are 2 Posting Method, real time & Daily posting (Default).<br />Support Off Line Mode.<br />Export Cash sales to pen Drive for your Accountant.<br />Accountant can Import Cash Sales from Pen Drive.
                val qty: Int = 0, // 1
                val rate: Int = 0, // 1499
                val rel_id: Int = 0, // 1
                val rel_type: String = "", // invoice
                val unit: String = "" // UNIT
            )

            data class Payment(
                val active: Int = 0, // 1
                val amount: Int = 0, // 1499
                val date: String = "", // 2021-01-16
                val daterecorded: String = "", // 2021-01-16 14:03:06
                val description: String = "",
                val expenses_only: Int = 0, // 0
                val id: Int = 0, // 1
                val invoiceid: Int = 0, // 1
                val invoices_only: Int = 0, // 0
                val name: String = "", // Bank
                val note: String = "",
                val paymentid: Int = 0, // 1
                val paymentmethod: String? = "", // null
                val paymentmode: Int = 0, // 1
                val selected_by_default: Int = 0, // 1
                val show_on_pdf: Int = 0, // 1
                val transactionid: String = "" // 80XVJ1610776986
            )
        }

        data class Member(
            val active: Int = 0, // 1
            val admin: Int = 0, // 0
            val datecreated: String = "", // 2023-02-22 23:54:13
            val default_language: String? = "", // null
            val direction: String? = "", // null
            val email: String = "", // staffone@gmail.com
            val email_signature: String? = "", // null
            val facebook: String? = "", // null
            val firstname: String = "", // Staff
            val full_name: String = "", // Staff One
            val google_auth_secret: String? = "", // null
            val hourly_rate: Int = 0, // 0
            val is_not_staff: Int = 0, // 0
            val last_activity: String = "", // 2023-03-03 11:12:50
            val last_email_check: String? = "", // null
            val last_ip: String = "", // 127.0.0.1
            val last_login: String = "", // 2023-02-26 13:09:20
            val last_password_change: String? = "", // 2021-02-17 20:03:10
            val lastname: String = "", // One
            val linkedin: String? = "", // null
            val mail_password: String? = "", // Hubert!123
            val mail_signature: String? = "", // <p>--</p><div class="gmail_signature" data-smartmail="gmail_signature" dir="ltr">    <div dir="ltr">        <div dir="ltr">            <div dir="ltr">                <div dir="ltr">                    <div dir="ltr">Thank you&lt;div dir="
            val media_path_slug: String = "", // staff-one
            val new_pass_key: String? = "", // 493ad3c0f86e0b102bd38ef18c454c5b
            val new_pass_key_requested: String? = "", // 2021-02-17 20:00:24
            val password: String = "", // $2a$08$D/CMJSyaYHEFk.GP52C5SONmNpvQY8ZoKm4eI.3oPaLKztIrTiPgG
            val phonenumber: Long? = 0, // 60168774145
            val profile_image: String? = "", // a96dacf3eb7a7765d54c4cd8927c2ef9.jpg
            val role: Int = 0, // 1
            val skype: String? = "", // null
            val staffid: Int = 0, // 16
            val two_factor_auth_code: String? = "", // null
            val two_factor_auth_code_requested: String? = "", // null
            val two_factor_auth_enabled: Int = 0 // 0
        )

        data class Payment(
            val active: Int = 0, // 1
            val amount: Int = 0, // 1499
            val date: String = "", // 2021-01-16
            val daterecorded: String = "", // 2021-01-16 14:03:06
            val description: String? = "", // null
            val expenses_only: Int = 0, // 0
            val id: Int = 0, // 1
            val invoiceid: Int = 0, // 1
            val invoices_only: Int = 0, // 0
            val name: String = "", // Bank
            val note: String? = "", // null
            val paymentid: Int = 0, // 1
            val paymentmethod: String? = "", // null
            val paymentmode: Int = 0, // 1
            val selected_by_default: Int = 0, // 1
            val show_on_pdf: Int = 0, // 1
            val transactionid: String = "" // 80XVJ1610776986
        )

        data class Template(
            val active: Int = 0, // 0
            val emailtemplateid: Int = 0, // 2
            val fromemail: String = "",
            val fromname: String = "", // {companyname} | CRM
            val language: String = "", // english
            val message: String = "", // <span style="font-size: 12pt;">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style="font-size: 12pt;">We have prepared the following invoice for you: <strong># {invoice_number}</strong></span><br /><br /><span style="font-size: 12pt;"><strong>Invoice status</strong>: {invoice_status}</span><br /><br /><span style="font-size: 12pt;">You can view the invoice on the following link: <a href="{invoice_link}">{invoice_number}</a></span><br /><br /><span style="font-size: 12pt;">Please contact us for more information.</span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span>
            val name: String = "", // Send Invoice to Customer
            val order: Int = 0, // 0
            val plaintext: Int = 0, // 0
            val slug: String = "", // invoice-send-to-client
            val subject: String = "", // Invoice with number {invoice_number} created
            val type: String = "" // invoice
        )

        data class CompanyInformation(
            val invoice_company_name: String? = "",
            val invoice_company_address: String? = "",
            val invoice_company_city: String? = "",
            val invoice_company_country_code: String? = "",
            val invoice_company_postal_code: String? = "",
            val invoice_company_phonenumber: String? = ""
        )
    }
}