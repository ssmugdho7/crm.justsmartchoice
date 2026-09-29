package com.divesh.perfex.sales.domain.models.item

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class Item(
    val id: Int = 0,
    val description: String? = "",
    val long_description: String? = "",
    val unit: String? = "",
    var rate: Double = 0.0
) : Parcelable