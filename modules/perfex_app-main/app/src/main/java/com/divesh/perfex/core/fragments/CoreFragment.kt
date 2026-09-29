package com.divesh.perfex.core.fragments


import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import androidx.activity.result.contract.ActivityResultContracts
import androidx.fragment.app.Fragment

abstract class CoreFragment : Fragment()  {
    protected var hasInternet = false
    private var requestSinglePermission = registerForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permissions ->
        onPermissionsChanged(permissions)
    }

    /*protected fun hasPermission(permission: String, mContext: Context): Boolean{
        return ContextCompat.checkSelfPermission(mContext,permission) == PackageManager.PERMISSION_GRANTED
    }*/

    protected fun askPermissions(permissions: Array<String>){
        requestSinglePermission.launch(permissions)
    }

    protected fun registerInternetConnectionReceiver() {
        val networkRequest = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .addTransportType(NetworkCapabilities.TRANSPORT_WIFI)
            .addTransportType(NetworkCapabilities.TRANSPORT_CELLULAR)
            .build()

        val networkCallback = object : ConnectivityManager.NetworkCallback() {
            // network is available for use
            override fun onAvailable(network: Network) {
                super.onAvailable(network)
                hasInternet = true
            }

            // lost network connection
            override fun onLost(network: Network) {
                super.onLost(network)
                hasInternet = false
            }
        }

        val connectivityManager = context?.getSystemService(ConnectivityManager::class.java) as ConnectivityManager
        connectivityManager.requestNetwork(networkRequest, networkCallback)
    }

    abstract fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>)
}