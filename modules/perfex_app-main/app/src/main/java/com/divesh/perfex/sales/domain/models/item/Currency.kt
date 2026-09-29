package com.divesh.perfex.sales.domain.models.item

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class Currency(
    var decimal_separator: String? = null, // .
    var id: Int = 0, // 1
    var isdefault: Int? = null, // 1
    var name: String = "", // USD
    var placement: String? = null, // before
    var symbol: String? = null, // $
    var thousand_separator: String? = null // ,
) : Parcelable