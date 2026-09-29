package com.divesh.perfex.notifications.presentation.manage

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.ProgressBar
import android.widget.Toast
import androidx.activity.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.activities.MainActivity
import com.divesh.perfex.databinding.ActivityNotficationsBinding
import com.divesh.perfex.notifications.domain.adapters.ManageNotificationsAdapter
import com.divesh.perfex.notifications.domain.interfaces.NotificationInterface
import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class NotificationsActivity : AppCompatActivity(), NotificationInterface {
    private lateinit var binding: ActivityNotficationsBinding
    private val viewModel: NotificationsViewModel by viewModels()
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = ManageNotificationsAdapter(arrayListOf(), this)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityNotficationsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        initViews(binding.root)
        observeViewModel(binding.root)
        initClicks()
    }

    private fun initViews(view: View) {
        lottieAnimation = view.findViewById(R.id.noDataFound)
        progressBar = binding.progressBar
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.refresh()
            binding.swipeRefresh.isRefreshing = false
        }
    }

    private fun observeViewModel(view: View) {
        viewModel.currentRecords.observe(this) { notifications ->
            notifications?.let {
                val lottieAnimation = view.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (it.isEmpty()) {
                    lottieAnimation?.visibility = View.VISIBLE
                    binding.swipeRefresh.visibility = View.GONE
                } else {
                    listAdapter.setRecords(it)
                    binding.swipeRefresh.visibility = View.VISIBLE
                    lottieAnimation?.visibility = View.GONE
                }
            }
        }
        viewModel.loadError.observe(this) { isError ->
            if (!isError.equals("") && isError != null) {
                progressBar.visibility = View.GONE
                Toast.makeText(this, isError.toString(), Toast.LENGTH_SHORT).show()
            }
        }

        viewModel.loading.observe(this) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }

        viewModel.notificationMarkedAsRead.observe(this){
            if(it != null){
                if(it){
                    viewModel.refresh()
                }
            }
        }
    }

    private fun initClicks(){
        binding.toolbarBack.setOnClickListener{
            startActivity(Intent(this, MainActivity::class.java))
            finish()
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh()
    }

    override fun markAsRead(notification: NotificationsResponseModel.Notification) {
        viewModel.markNotificationAsRead(notification.id)
    }
}