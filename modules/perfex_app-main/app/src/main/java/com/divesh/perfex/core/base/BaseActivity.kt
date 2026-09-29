package com.divesh.perfex.core.base

import android.content.Context
import android.content.ContextWrapper
import android.content.pm.PackageManager
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.divesh.perfex.core.helpers.Extensions
import java.util.*

abstract class BaseActivity: AppCompatActivity() {
    private var requestSinglePermission = registerForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permissions ->
        onPermissionsChanged(permissions)
    }

    override fun attachBaseContext(newBase: Context) {
        val locale = Extensions().getAppLocale(newBase)
        if(locale == null) {
            super.attachBaseContext(ContextWrapper(newBase.setAppLocale("en")))
        }else{
            super.attachBaseContext(ContextWrapper(newBase.setAppLocale(locale)))
        }
    }

    private fun Context.setAppLocale(language: String): Context{
        val locale = Locale(language)
        Locale.setDefault(locale)
        val config = resources.configuration
        config.setLocale(locale)
        config.setLayoutDirection(locale)
        return createConfigurationContext(config)
    }

    protected fun hasPermission(permission: String, mContext: Context): Boolean{
        return ContextCompat.checkSelfPermission(mContext,permission) == PackageManager.PERMISSION_GRANTED
    }

    protected fun askPermissions(permissions: Array<String>){
        requestSinglePermission.launch(permissions)
    }

    abstract fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>)
}