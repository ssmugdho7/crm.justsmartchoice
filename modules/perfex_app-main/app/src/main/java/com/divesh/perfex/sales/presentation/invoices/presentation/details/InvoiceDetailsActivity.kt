package com.divesh.perfex.sales.presentation.invoices.presentation.details

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.util.Log
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.databinding.ActivityInvoiceDetailsBinding
import com.divesh.perfex.sales.domain.adapters.invoices.InvoicePagerAdapter
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class InvoiceDetailsActivity : AppCompatActivity() {
    private lateinit var binding: ActivityInvoiceDetailsBinding
    private val listOfTitles = arrayListOf<String>()
    private lateinit var viewPager: ViewPager2
    private lateinit var tabLayout: TabLayout
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityInvoiceDetailsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        tabLayout = findViewById(R.id.tab_layout)
        tabLayout.tabMode = TabLayout.MODE_AUTO
        viewPager = binding.viewPager
        loadTitles()
        setUpViewPagerWithTabLayout()
    }

    private fun setUpViewPagerWithTabLayout() {
        val pagerAdapter = InvoicePagerAdapter(this, listOfTitles, intent)
        viewPager.adapter = pagerAdapter
        addTabLayoutMediator()
    }

    private fun addTabLayoutMediator() {
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_info))
    }
}