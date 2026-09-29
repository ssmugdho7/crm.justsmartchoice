package com.divesh.perfex.core.helpers

import android.os.Build
import java.text.SimpleDateFormat
import java.util.*

class DateHelper {

    fun timeAgoMain(timeReceived: Long): String {
        val secondMillis = 1000
        val minuteMillis = 60 * secondMillis
        val hourMillis = 60 * minuteMillis
        val dayMillis = 24 * hourMillis
        val weekMillis = 7 * dayMillis
        var time = timeReceived
        if (timeReceived < 1000000000000L) {
            // if timestamp given in seconds, convert to millis
            time *= 1000
        }

        val now = System.currentTimeMillis()
        val diff = now - time
        return if (diff > 0) {
            when {
                diff < minuteMillis -> {
                    "Just now"
                }
                diff < 2 * minuteMillis -> {
                    "A minute ago"
                }
                diff < 50 * minuteMillis -> {
                    "${diff / minuteMillis} minutes ago"
                }
                diff < 90 * minuteMillis -> {
                    "An hour ago"
                }
                diff < 24 * hourMillis -> {
                    "${diff / hourMillis} hours ago"
                }
                diff < 48 * hourMillis -> {
                    "Yesterday"
                }
                diff < 7 * dayMillis -> {
                    "${diff / dayMillis} days ago"
                }
                diff < 2 * weekMillis -> {
                    "A week ago"
                }
                diff < weekMillis * 3 -> {
                    "${diff / weekMillis} weeks ago"
                }
                else -> {
                    val date = Date(time)
                    date.toString()
                }
            }
        } else {
            "Today"
        }
    }
    /*
    fun timeAgo(timeReceived: Long): String {
        if(DateUtils.isToday(timeReceived)){
            return "Today"
        }

        val receivedDateString = SimpleDateFormat(
            "dd/MMMM/yyyy",
            Locale.getDefault()
        ).format(Date(timeReceived))

        val calendar = Calendar.getInstance()
        calendar.add(Calendar.DATE, -1)
        val yesterday = calendar.time

        val yesterdayDateString = SimpleDateFormat(
            "dd/MMMM/yyyy",
            Locale.getDefault()
        ).format(yesterday)

        if(receivedDateString == yesterdayDateString){
            return "Yesterday"
        }

        return ""
    */
    fun dateTimeFromLongTime(timeReceived: Long): String{
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            SimpleDateFormat("dd-MMM-yy HH:mm aa", Locale.ENGLISH).format(Date(timeReceived)).toString()
        } else {
            SimpleDateFormat("dd-MMM-yyyy HH:mm aa", Locale.ENGLISH).format(Date(timeReceived)).toString()
        }
    }
}