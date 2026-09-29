package com.divesh.perfex.core.activities

import android.content.Intent
import android.graphics.drawable.ColorDrawable
import android.os.Bundle
import android.util.Log
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.view.WindowManager
import android.widget.ImageView
import android.widget.Toast
import androidx.activity.addCallback
import androidx.activity.viewModels
import androidx.appcompat.app.ActionBarDrawerToggle
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.widget.AppCompatTextView
import androidx.core.content.ContextCompat
import androidx.drawerlayout.widget.DrawerLayout
import androidx.viewpager2.widget.ViewPager2
import coil.load
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import com.divesh.perfex.R
import com.divesh.perfex.core.adapters.MainViewPagerAdapter
import com.divesh.perfex.core.base.BaseActivity
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.view_models.MainActivityViewModel
import com.divesh.perfex.databinding.ActivityMainBinding
import com.divesh.perfex.login.presentation.LoginActivity
import com.divesh.perfex.notifications.presentation.manage.NotificationsActivity
import com.divesh.perfex.sales.presentation.manage.SalesActivity
import com.divesh.perfex.settings.presentation.SettingsActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class MainActivity : BaseActivity() {
    private val listOfTitles = arrayListOf<String>()
    private lateinit var binding: ActivityMainBinding
    private lateinit var viewPager: ViewPager2
    private lateinit var tabLayout: TabLayout
    private val viewModel: MainActivityViewModel by viewModels()
    private var pressedTime: Long = 0
    private lateinit var drawerLayout: DrawerLayout
    private lateinit var actionBarDrawerToggle: ActionBarDrawerToggle

    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setupNavigationDrawer()
        tabLayout = findViewById(R.id.tab_layout)
        viewPager = binding.viewPager
        setUpViewPagerWithTabLayout()
        observeViewModel()
        onBackPressedDispatcher.addCallback(this) {
            if (pressedTime + 2000 > System.currentTimeMillis()) {
                finish()
            } else {
                Toast.makeText(baseContext, "Press back again to exit", Toast.LENGTH_SHORT).show()
            }
            pressedTime = System.currentTimeMillis()
        }
    }

    private fun setupNavigationDrawer() {
        drawerLayout = binding.myDrawerLayout
        actionBarDrawerToggle = ActionBarDrawerToggle(
            this,
            drawerLayout,
            binding.toolbar,
            R.string.nav_open,
            R.string.nav_close
        )
        actionBarDrawerToggle.isDrawerIndicatorEnabled = true
        actionBarDrawerToggle.drawerArrowDrawable.color =
            ContextCompat.getColor(this, R.color.white)
        drawerLayout.addDrawerListener(actionBarDrawerToggle)
        actionBarDrawerToggle.syncState()
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayHomeAsUpEnabled(true)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        binding.navigationView.setNavigationItemSelectedListener { item ->
            when (item.itemId) {
                R.id.action_settings -> {
                    startActivity(Intent(this@MainActivity, SettingsActivity::class.java))
                }
                R.id.action_sales -> {
                    startActivity(Intent(this@MainActivity, SalesActivity::class.java))
                }
            }
            true
        }
    }

    private fun observeViewModel() {
        viewModel.loggedIn.observe(this) {
            if (!it) {
                startActivity(Intent(this, LoginActivity::class.java))
                finish()
            }
        }
        viewModel.name.observe(this) {
            binding.titleBarTv.text = it
            val headerView: View = binding.navigationView.getHeaderView(0)
            headerView.findViewById<AppCompatTextView>(R.id.profileName).text = it
        }
        viewModel.email.observe(this) {
            val headerView: View = binding.navigationView.getHeaderView(0)
            headerView.findViewById<AppCompatTextView>(R.id.email).text = it
        }
        viewModel.profileImage.observe(this) {
            val headerView: View = binding.navigationView.getHeaderView(0)
            val imageView = headerView.findViewById<ImageView>(R.id.staffImage)
            var url = "${Constants.BASE_URL}/assets/images/user-placeholder.jpg"
            if(it.isNotBlank() && it != "null" && it != ""){
                url = "${Constants.BASE_URL}/uploads/staff_profile_images/${viewModel.staffId.value}/thumb_$it"
            }
            Log.d(Constants.universalLogTag, url)
            imageView.load(url){
                crossfade(true)
                placeholder(R.drawable.loading_image)
            }
        }
    }

    private fun updateColorIfTabChanged(position: Int) {
        val window = this.window
        window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS)
        if (position == 1) {
            binding.toolbar.background =
                ColorDrawable(resources.getColor(R.color.ForestGreen, null))
            binding.tabLayout.root.background =
                ColorDrawable(resources.getColor(R.color.ForestGreen, null))
            binding.rootLayout.background =
                ColorDrawable(resources.getColor(R.color.ForestGreen, null))
            window.statusBarColor = this.resources.getColor(R.color.GreenOnion, null)
        } else {
            binding.toolbar.background =
                ColorDrawable(resources.getColor(R.color.themeColorPrimary, null))
            binding.tabLayout.root.background =
                ColorDrawable(resources.getColor(R.color.themeColorPrimary, null))
            binding.rootLayout.background =
                ColorDrawable(resources.getColor(R.color.themeColorPrimary, null))
            window.statusBarColor = this.resources.getColor(R.color.themeColorSecondary, null)
        }
    }

    private fun setUpViewPagerWithTabLayout() {
        loadTitles()
        val currentItem = when (intent.getStringExtra("comingFrom").toString()) {
            getString(R.string.tab_dashboard) -> {
                0
            }
            getString(R.string.tab_customers) -> {
                1
            }
            getString(R.string.tab_tasks) -> {
                2
            }
            getString(R.string.tab_support) -> {
                3
            }
            getString(R.string.tab_leads) -> {
                4
            }
            else -> {
                0
            }
        }

        val pagerAdapter = MainViewPagerAdapter(this, listOfTitles)
        viewPager.adapter = pagerAdapter
        viewPager.currentItem = currentItem
        addTabLayoutMediator()

        viewPager.registerOnPageChangeCallback(object : ViewPager2.OnPageChangeCallback() {
            override fun onPageSelected(position: Int) {
                super.onPageSelected(position)
                updateColorIfTabChanged(position)
            }
        })

        updateColorIfTabChanged(currentItem)
    }

    private fun addTabLayoutMediator() {
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_dashboard))
        listOfTitles.add(getString(R.string.tab_customers))
        listOfTitles.add(getString(R.string.tab_tasks))
        listOfTitles.add(getString(R.string.tab_support))
        listOfTitles.add(getString(R.string.tab_leads))
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.main_menu, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (actionBarDrawerToggle.onOptionsItemSelected(item)) {
            return true;
        }
        when (item.itemId) {
            R.id.action_notification -> {
                startActivity(Intent(this, NotificationsActivity::class.java))
            }
            R.id.action_logout -> {
                val builder = AlertDialog.Builder(this)
                builder.setMessage(getString(R.string.confirm_logout))
                    .setCancelable(false)
                    .setPositiveButton("Yes") { _, _ ->
                        viewModel.logoutUser()
                    }
                    .setNegativeButton("No") { dialog, _ ->
                        // Dismiss the dialog
                        dialog.dismiss()
                    }
                val alert = builder.create()
                alert.show()
            }
        }
        return super.onOptionsItemSelected(item)
    }
}