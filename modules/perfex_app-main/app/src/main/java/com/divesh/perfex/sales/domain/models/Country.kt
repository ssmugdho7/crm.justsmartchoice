package com.divesh.perfex.sales.domain.models

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class Country(
    val country_id: Int = 0,
    val short_name: String? = "",
    val calling_code: String? = ""
) : Parcelable