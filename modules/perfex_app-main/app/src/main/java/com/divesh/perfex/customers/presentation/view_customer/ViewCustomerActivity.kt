package com.divesh.perfex.customers.presentation.view_customer

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.widget.ImageView
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.customers.domain.adapters.ViewCustomerPagerAdapter
import com.divesh.perfex.databinding.ActivityViewCustomerBinding
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ViewCustomerActivity : AppCompatActivity() {
    private lateinit var binding: ActivityViewCustomerBinding
    private lateinit var viewPager: ViewPager2
    private val listOfTitles = arrayListOf<String>()
    private lateinit var tabLayout: TabLayout
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewCustomerBinding.inflate(layoutInflater)
        val customerId = intent.getIntExtra("userId", 0)
        val createContact = intent.getBooleanExtra("createContact", false)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        tabLayout = binding.tabLayout
        viewPager = binding.viewPager
        setUpViewPagerWithTabLayout(customerId, createContact)

        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = "#$customerId"
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
    }

    private fun setUpViewPagerWithTabLayout(userId: Int, createContact: Boolean) {
        loadTitles()
        val pagerAdapter = ViewCustomerPagerAdapter(this, listOfTitles.size, userId)
        viewPager.adapter = pagerAdapter
        viewPager.currentItem = if(createContact) 1 else 0
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_profile))
        listOfTitles.add(getString(R.string.tab_contacts))
        listOfTitles.add(getString(R.string.tab_notes))
//        listOfTitles.add(getString(R.string.tab_invoices))
//        listOfTitles.add(getString(R.string.tab_proposals))
        listOfTitles.add(getString(R.string.tab_tasks))
    }
}