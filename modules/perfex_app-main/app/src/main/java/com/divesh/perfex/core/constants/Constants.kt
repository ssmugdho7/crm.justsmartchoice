package com.divesh.perfex.core.constants

class Constants {
    companion object {
        //api configurations
        private const val API_SUFFIX = "/perfex_mobile_app_api/"
        const val BASE_URL = "http://192.168.139.133/perfex"
        const val API_URL = "$BASE_URL$API_SUFFIX"
        const val AUTHENTICATION_KEY = "Auth-Key"
        const val AUTHENTICATION_VALUE = "J8sxPqLQdWijDJvSro3FKmMNJVyG0vUDSAqSwyeYaW4xx2JGGpxs44CQlXodYPsf"
        const val channelId = "454545"

        //shared preferences
        const val packagePreFix = "com.divesh.perfex"
        const val loginStatus = "${packagePreFix}.LOGIN_STATUS"
        const val staffId = "${packagePreFix}.STAFF_ID"
        const val firstName = "${packagePreFix}.FIRST_NAME"
        const val lastName = "${packagePreFix}.LAST_NAME"
        const val emailId = "${packagePreFix}.EMAIL_ID"
        const val isAdmin = "${packagePreFix}.IS_ADMIN"
        const val phoneNumber = "${packagePreFix}.PHONE_NUMBER"
        const val profileImage = "${packagePreFix}.profileImage"
        const val authenticationToken = "${packagePreFix}.authenticationToken"

        const val isConnectedWithPusher = "${packagePreFix}.isConnectedWithPusher"
        const val isRegisteredOnPusher = "${packagePreFix}.isRegisteredOnPusher"
        const val pusherAppKey = "${packagePreFix}.pusherAppKey"
        const val pusherCluster = "${packagePreFix}.pusherCluster"

        const val defaultLanguage = "${packagePreFix}.defaultLanguage"
        const val universalLogTag = "CustomLogger"
    }
}
