package com.divesh.perfex.login.presentation

import android.content.Intent
import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.view.Window
import android.view.WindowManager
import android.widget.ProgressBar
import androidx.activity.viewModels
import androidx.core.content.ContextCompat
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.core.activities.CoreActivity
import com.divesh.perfex.core.activities.MainActivity
import com.divesh.perfex.databinding.ActivityLoginBinding
import com.google.android.material.snackbar.Snackbar
import dagger.hilt.android.AndroidEntryPoint
import java.util.*

@AndroidEntryPoint
class LoginActivity : CoreActivity() {
    private val loginViewModel: LoginViewModel by viewModels()
    private lateinit var binding: ActivityLoginBinding
    private lateinit var progressBar : ProgressBar
    private val emailPattern = "[a-zA-Z0-9._-]+@[a-z]+\\.+[a-z]+"
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        loginViewModel.isLoggedIn.observe(this){
            if(it) {
                loginSuccess()
            }
        }
        registerInternetConnectionReceiver()
        setContentView(binding.root)
        initViews()
        initClicks()
        observeViewModel()
    }

    private fun observeViewModel() {
        loginViewModel.errorMessage.observe(this){
            if (it != null){
                showSnackBar(it)
                binding.progressBar.visibility = View.GONE
            }
        }

        loginViewModel.metaDataResponse.observe(this){
            if(it != null){
                if(it.companyLogo?.isNotBlank() == true){
                    binding.companyLogo.load(it.companyLogo){
                        crossfade(true)
                        placeholder(R.drawable.loading_image)
                    }
                }
                if(it.company_name?.isNotBlank() == true){
                    binding.appName.text = it.company_name
                }
            }
        }
    }

    private fun initClicks() {
        binding.loginBtn.setOnClickListener{
            validateAndLogin()
        }
    }

    private fun validateAndLogin() {
        val isValid = validateCredentials()
        if (isValid) {
            binding.progressBar.visibility = View.VISIBLE
            loginViewModel.login(binding.email.editText?.text.toString(), binding.password.editText?.text.toString())
        }
    }

    private fun loginSuccess() {
        binding.progressBar.visibility = View.GONE
        val intent = Intent(this@LoginActivity, MainActivity::class.java)
        startActivity(intent)
        finish()
    }

    private fun validateCredentials(): Boolean {
        var isValid = true
        if(!hasInternet){
            showSnackBar(getString(R.string.no_internet))
            isValid = false
        }
        if (binding.email.editText?.text.toString().isBlank()) {
            showSnackBar("Please enter a valid email!")
            binding.email.editText?.error = "Please enter a valid email!"
            isValid = false
        }

        if (binding.password.editText?.text.isNullOrBlank()) {
            showSnackBar("Please enter a valid password!")
            binding.password.editText?.error = "Please enter a valid password!"
            isValid = false
        }
        return isValid
    }

    private fun showSnackBar(title: String) {
        val snackBar = Snackbar.make(binding.rootView, title,
            Snackbar.LENGTH_LONG).setAction("Action", null)
        snackBar.setActionTextColor(Color.BLACK)
        snackBar.show()
    }

    private fun initViews() {
        progressBar = binding.progressBar
        val greeting = getGreetingMessage()
        binding.greetingText.text = greeting

        if(greeting == "Evening" || greeting == "Night"){
            val window: Window = window
            window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS)
            window.statusBarColor = ContextCompat.getColor(this, R.color.BeetleGreen)
        }else{
            val window: Window = window
            window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS)
            window.statusBarColor = ContextCompat.getColor(this, R.color.BeetleGreen)
        }
    }

    private fun getGreetingMessage():String{
        val c = Calendar.getInstance()
        return when (c.get(Calendar.HOUR_OF_DAY)) {
            in 0..11 -> "Good Morning"
            in 12..15 -> "Good Afternoon"
            in 16..20 -> "Good Evening"
            in 21..23 -> "Good Night"
            else -> "Good Morning"
        }
    }
}