package com.divesh.perfex.sales.presentation.manage

import android.os.Bundle
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.core.base.BaseActivity
import com.divesh.perfex.databinding.ActivitySalesBinding
import com.divesh.perfex.sales.domain.adapters.SalesPagerAdapter
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class SalesActivity : BaseActivity() {
    private lateinit var binding: ActivitySalesBinding
    private val listOfTitles = arrayListOf<String>()
    private lateinit var viewPager: ViewPager2
    private lateinit var tabLayout: TabLayout
    override fun onPermissionsChanged(permissions: Map<String, Boolean>) {

    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySalesBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        tabLayout = findViewById(R.id.tab_layout)
        tabLayout.tabMode = TabLayout.MODE_AUTO
        viewPager = binding.viewPager
        binding.toolbarBack.setOnClickListener{
            onBackPressedDispatcher.onBackPressed()
        }
        loadTitles()
        setUpViewPagerWithTabLayout()
    }

    private fun setUpViewPagerWithTabLayout() {
        val pagerAdapter = SalesPagerAdapter(this, listOfTitles)
        viewPager.adapter = pagerAdapter
        addTabLayoutMediator()
    }

    private fun addTabLayoutMediator() {
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_proposals))
        listOfTitles.add(getString(R.string.tab_invoices))
        /*listOfTitles.add(getString(R.string.tab_estimates))
        listOfTitles.add(getString(R.string.tab_payments))
        listOfTitles.add(getString(R.string.tab_credit_notes))
        listOfTitles.add(getString(R.string.tab_items))*/
    }
}