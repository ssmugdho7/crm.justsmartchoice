package com.divesh.perfex.login.domain.models

import com.google.gson.annotations.SerializedName
data class LoginResponse(
    @SerializedName("success") val status : Int,
    @SerializedName("message") val message : String,
    @SerializedName("user") val data : Staff
)
