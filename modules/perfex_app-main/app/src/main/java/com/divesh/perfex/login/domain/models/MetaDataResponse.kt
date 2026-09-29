package com.divesh.perfex.login.domain.models

import com.google.gson.annotations.SerializedName

data class MetaDataResponse(
    @SerializedName("success") val status : Int,
    @SerializedName("message") val message : String,
    @SerializedName("companyLogo") val companyLogo : String?,
    @SerializedName("company_name") val company_name : String?,
)