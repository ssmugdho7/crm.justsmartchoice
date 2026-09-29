package com.divesh.perfex.leads.domain.models

import android.os.Parcelable
import com.google.gson.annotations.SerializedName
import kotlinx.parcelize.Parcelize

@Parcelize
data class Lead(
    @SerializedName("addedfrom") val addedfrom: Int = 0,
    @SerializedName("address") val address: String? = "",
    @SerializedName("assigned") val assigned: String? = "",
    @SerializedName("city") val city: String? = "",
    @SerializedName("client_id") val client_id: Int = 0,
    @SerializedName("company") val company: String? = "",
    @SerializedName("country") val country: Int = 0,
    @SerializedName("date_converted") val date_converted: String? = "",
    @SerializedName("dateadded") val dateadded: String? = "",
    @SerializedName("dateassigned") val dateassigned: String? = "",
    @SerializedName("default_language") val default_language: String? = "",
    @SerializedName("description") val description: String? = "",
    @SerializedName("email") val email: String? = "",
    @SerializedName("email_integration_uid") val email_integration_uid: String? = "",
    @SerializedName("from_form_id") val from_form_id: Int = 0,
    @SerializedName("hash") val hash: String? = "",
    @SerializedName("id") val id: Int = 0,
    @SerializedName("is_imported_from_email_integration") val is_imported_from_email_integration: Int = 0,
    @SerializedName("is_public") val is_public: Int = 0,
    @SerializedName("junk") val junk: Int = 0,
    @SerializedName("last_lead_status") val last_lead_status: Int = 0,
    @SerializedName("last_status_change") val last_status_change: String? = "",
    @SerializedName("lastcontact") val lastcontact: String? = "",
    @SerializedName("lead_value") val lead_value: String? = "",
    @SerializedName("leadorder") val leadorder: Int = 0,
    @SerializedName("lost") val lost: Int = 0,
    @SerializedName("name") val name: String? = "",
    @SerializedName("phonenumber") val phonenumber: String? = "",
    @SerializedName("source") val source: String? = "",
    @SerializedName("state") val state: String? = "",
    @SerializedName("status") val status: String? = "",
    @SerializedName("title") val title: String? = "",
    @SerializedName("website") val website: String? = "",
    @SerializedName("zip") val zip: String? = "",
    @SerializedName("countryName") val countryName: String? = ""
): Parcelable
