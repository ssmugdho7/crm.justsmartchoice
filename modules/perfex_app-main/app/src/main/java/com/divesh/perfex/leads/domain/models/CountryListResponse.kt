package com.divesh.perfex.leads.domain.models

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class CountryListResponse(
    val message: String? = "", val countries: List<Country> = listOf()
): Parcelable{
    @Parcelize
    data class Country(
        val calling_code: String? = "",
        val cctld: String? = "",
        val country_id: Int = 0,
        val iso2: String? = "",
        val iso3: String? = "",
        val long_name: String? = "",
        val numcode: String? = "",
        val short_name: String? = "",
        val un_member: String? = ""
    ):Parcelable
}