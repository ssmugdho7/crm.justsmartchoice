package com.divesh.perfex.leads.domain.models

import com.google.gson.annotations.SerializedName

data class LeadsResponseModel(
    @SerializedName("message") val message: String?,
    @SerializedName("leads") val leads: List<Lead>,
    @SerializedName("status") val status: Int
)