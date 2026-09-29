package com.divesh.perfex.sales.domain.models.item

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class Tax(
    val id: Int = 0,
    val name: String = "",
    val taxrate: Int = 0
) : Parcelable