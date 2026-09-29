package com.divesh.perfex.customers.domain.models

import android.os.Parcelable
import com.divesh.perfex.leads.domain.models.CountryListResponse
import kotlinx.parcelize.Parcelize

@Parcelize
data class CustomerResponseModel(
    val data: Data = Data(),
    val message: String? = "", // Customers found
    val status: Int = 0 // 1
) : Parcelable {
    @Parcelize
    data class Data(
        val client: Client = Client(),
        val contacts: List<Contact>? = listOf(),
        val currencies: List<Currency>? = listOf(),
        val customer_admins: List<CustomerAdmin>? = listOf(),
        val countries: List<CountryListResponse.Country>? = listOf(),
        val customer_currency: CustomerCurrency? = CustomerCurrency(),
        val customer_groups: List<CustomerGroup>? = listOf(),
        val group: String? = "", // profile
        val groups: List<Group>? = listOf(),
        val staff: List<Staff>? = listOf(),
        val zip_in_folder: String? = "" // test123
    ): Parcelable {
        @Parcelize
        data class Client(
            val active: String? = "", // 1
            val addedfrom: String? = "", // 1
            val address: String? = "", //
            val billing_city: String? = "", // Jaipur
            val billing_country: String? = "", // 102
            val billing_state: String? = "", // Rajasthan
            val billing_street: String? = "", //
            val billing_zip: String? = "", // 302017
            val city: String? = "", // Jaipur
            val company: String? = "", // test123
            val country: String? = "", // 102
            val datecreated: String? = "", // 2022-08-01 21:59:58
            val default_currency: String? = "", // 0
            val default_language: String? = "",
            val latitude: String? = "", // null
            val leadid: String? = "", // null
            val longitude: String? = "", // null
            val phonenumber: String? = "", // 7894561230
            val registration_confirmed: String? = "", // 1
            val shipping_city: String? = "", // Jaipur
            val shipping_country: String? = "", // 102
            val shipping_state: String? = "", // Rajasthan
            val shipping_street: String? = "", //
            val shipping_zip: String? = "", // 302017
            val show_primary_contact: String? = "", // 0
            val state: String? = "", // Rajasthan
            val stripe_id: String? = "", // null
            val userid: Int = 0, // 4
            val vat: String? = "", // 2342434
            val website: String? = "", // google.com
            val zip: String? = "" // 302017
        ):Parcelable

        data class Contact(
            val active: Int = 0, // 1
            val contract_emails: String? = "", // 1
            val credit_note_emails: String? = "", // 1
            val datecreated: String? = "", // 2022-08-02 08:49:26
            val direction: String? = "",
            val email: String? = "", // diveshahuja77@gmail.com
            val email_verification_key: String? = "", // null
            val email_verification_sent_at: String? = "", // null
            val email_verified_at: String? = "", // 2022-08-02 08:49:26
            val estimate_emails: String? = "", // 1
            val firstname: String? = "", // Divesh
            val id: Int = 0, // 1
            val invoice_emails: String? = "", // 1
            val is_primary: String? = "", // 1
            val last_ip: String? = "", // null
            val last_login: String? = "", // null
            val last_password_change: String? = "", // null
            val lastname: String? = "", // Ahuja
            val new_pass_key: String? = "", // null
            val new_pass_key_requested: String? = "", // null
            val password: String? = "", // $2a$08$2imwCF5/UpUlglxOt3mBF.Fup1CDe0UYWSx4OAiinOv3G.vbj0WWm
            val phonenumber: String? = "", //
            val profile_image: String? = "", // null
            val project_emails: String? = "", // 1
            val task_emails: String? = "", // 1
            val ticket_emails: String? = "", // 1
            val title: String? = "", // Manager
            val userid: String? = "" // 4
        ): java.io.Serializable
        @Parcelize
        data class Currency(
            val decimal_separator: String? = "", // .
            val id: String? = "", // 1
            val isdefault: String? = "", // 1
            val name: String? = "", // USD
            val placement: String? = "", // before
            val symbol: String? = "", // $
            val thousand_separator: String? = "" // ,
        ):Parcelable

        @Parcelize
        data class CustomerAdmin(
            val customer_id: String? = "", // 4
            val date_assigned: String? = "", // 2022-08-02 08:58:20
            val staff_id: String? = "" // 1
        ):Parcelable

        @Parcelize
        data class CustomerCurrency(
            val decimal_separator: String? = "", // .
            val id: String? = "", // 1
            val isdefault: String? = "", // 1
            val name: String? = "", // USD
            val placement: String? = "", // before
            val symbol: String? = "", // $
            val thousand_separator: String? = "" // ,
        ): Parcelable

        @Parcelize
        data class CustomerGroup(
            val customer_id: String? = "", // 4
            val groupid: String? = "", // 1
            val id: String? = "" // 2
        ):Parcelable

        @Parcelize
        data class Group(
            val id: Int = 0, // 1
            val name: String? = "" // DEMO1
        ): Parcelable

        @Parcelize
        data class Staff(
            val active: String? = "", // 1
            val admin: String? = "", // 0
            val datecreated: String? = "", // 2022-08-02 08:59:41
            val default_language: String? = "",
            val direction: String? = "",
            val email: String? = "", // staffone@gmail.com
            val email_signature: String? = "",
            val facebook: String? = "",
            val firstname: String? = "", // Staff
            val full_name: String? = "", // Staff one
            val google_auth_secret: String? = "", // null
            val hourly_rate: String? = "", // 0
            val is_not_staff: String? = "", // 0
            val last_activity: String? = "", // 2022-08-02 08:59:43
            val last_ip: String? = "", // ::1
            val last_login: String? = "", // 2022-08-02 08:47:42
            val last_password_change: String? = "", // null
            val lastname: String? = "", // one
            val linkedin: String? = "",
            val media_path_slug: String? = "", // staff-one
            val new_pass_key: String? = "", // null
            val new_pass_key_requested: String? = "", // null
            val password: String? = "", // $2a$08$XSLENfVmZZeal2E7qekE1eBcvABzxieDHhtyrBJEhq1u.GIr8TZO6
            val phonenumber: String? = "",
            val profile_image: String? = "", // null
            val role: String? = "", // 1
            val skype: String? = "",
            val staffid: String? = "", // 2
            val two_factor_auth_code: String? = "", // null
            val two_factor_auth_code_requested: String? = "", // null
            val two_factor_auth_enabled: String? = "" // 0
        ): Parcelable
    }
}