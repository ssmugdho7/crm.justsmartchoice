package com.divesh.perfex.login.domain.models

import android.os.Parcelable
import com.google.gson.annotations.SerializedName
import kotlinx.parcelize.Parcelize

@Parcelize
data class Staff(
    @SerializedName("staffid") val staffid : Int,
    @SerializedName("email") val email : String,
    @SerializedName("firstname") val firstname : String,
    @SerializedName("lastname") val lastname : String?,
    @SerializedName("phonenumber") val phonenumber : String?,
    @SerializedName("profile_image") val profile_image : String?,
    @SerializedName("authentication_token") val authentication_token : String,
    @SerializedName("admin") val admin : Int
): Parcelable