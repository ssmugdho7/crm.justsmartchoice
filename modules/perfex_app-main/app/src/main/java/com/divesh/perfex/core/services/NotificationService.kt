package com.divesh.perfex.core.services

import android.Manifest
import android.app.PendingIntent
import android.app.Service
import android.content.Intent
import android.content.SharedPreferences
import android.content.pm.PackageManager
import android.os.IBinder
import androidx.core.app.ActivityCompat
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.notifications.data.repositories.NotificationsApiRepository
import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel
import com.divesh.perfex.notifications.presentation.manage.NotificationsActivity
import com.pusher.client.Pusher
import com.pusher.client.PusherOptions
import com.pusher.client.channel.Channel
import com.pusher.client.channel.PusherEvent
import com.pusher.client.connection.ConnectionEventListener
import com.pusher.client.connection.ConnectionState
import com.pusher.client.connection.ConnectionStateChange
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject
import kotlin.random.Random

@AndroidEntryPoint
class NotificationService : Service() {
    private var startMode = 0
    private var binder: IBinder? = null
    private var allowRebind = false
    override fun onBind(intent: Intent?): IBinder? {
        return binder
    }

    @Inject
    lateinit var preferencesFile: SharedPreferences

    @Inject
    lateinit var notificationsApiRepository: NotificationsApiRepository

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        subscribeAndListenToNotification()
        return startMode
    }

    override fun onUnbind(intent: Intent?): Boolean {
        return allowRebind
    }

    override fun onDestroy() {
        // The service is no longer used and is being destroyed
    }

    private fun subscribeAndListenToNotification() {
        val pusherAppKey = preferencesFile.getString(Constants.pusherAppKey, "")
        val pusherCluster = preferencesFile.getString(Constants.pusherCluster, "")
        if (pusherCluster != "" && pusherAppKey != "") {
            val options = PusherOptions()
            options.setCluster(pusherCluster)

            val pusher = Pusher(pusherAppKey, options)
            pusher.connect(object : ConnectionEventListener {
                override fun onConnectionStateChange(change: ConnectionStateChange) {
                    if (change.currentState == ConnectionState.CONNECTED) {
                        preferencesFile.edit().putString(Constants.isConnectedWithPusher, "1")
                            .apply()
                    }
                }

                override fun onError(message: String?, code: String?, e: java.lang.Exception?) {
                }
            }, ConnectionState.ALL)

            val channel: Channel = pusher.subscribe(
                "notifications-channel-${
                    preferencesFile.getString(
                        Constants.authenticationToken, ""
                    )
                }"
            )
            channel.bind(
                "notification"
            ) { event ->
                getLatestNotification(event)
            }
        }
    }

    private fun getLatestNotification(event: PusherEvent) {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = notificationsApiRepository.getLatestNotification(
                    preferencesFile.getString(Constants.authenticationToken, "").toString()
                )
                if (response != null) {
                    if (response.isSuccessful) {
                        withContext(Dispatchers.Main) {
                            if (response.body()?.notifications?.isNotEmpty() == true) {
                                showNotification(response.body()?.notifications!![0])
                            }
                        }
                    }
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    private fun showNotification(notification: NotificationsResponseModel.Notification?) {
        if (notification != null) {
            // Create an explicit intent for an Activity in your app
            val intent = Intent(this, NotificationsActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
            }
            val pendingIntent: PendingIntent =
                PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_IMMUTABLE)

            val title = if (notification.from_fullname != null) {
                "New notification from ${notification.from_fullname}"
            } else {
                "Notification arrived!"
            }
            val builder = NotificationCompat.Builder(this, Constants.channelId)
                .setSmallIcon(R.drawable.ic_baseline_task_24)
                .setContentTitle(title)
                .setContentText(notification.description)
                .setPriority(NotificationCompat.PRIORITY_DEFAULT)
                // Set the intent that will fire when the user taps the notification
                .setContentIntent(pendingIntent)
                .setAutoCancel(true)
            with(NotificationManagerCompat.from(this)) {
                // notificationId is a unique int for each notification that you must define
                if (ActivityCompat.checkSelfPermission(
                        applicationContext,
                        Manifest.permission.POST_NOTIFICATIONS
                    ) != PackageManager.PERMISSION_GRANTED
                ) {
                    return
                }
                notify(Random.nextInt(300), builder.build())
            }
        }
    }
}