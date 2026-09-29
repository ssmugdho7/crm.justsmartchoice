package com.divesh.perfex.tasks.presentation.view

import android.os.Bundle
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.core.base.BaseActivity
import com.divesh.perfex.databinding.ActivityViewTaskBinding
import com.divesh.perfex.tasks.domain.adapters.ViewTasksPagerAdapter
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ViewTaskActivity : BaseActivity() {
    private lateinit var binding: ActivityViewTaskBinding
    private val listOfTitles = arrayListOf<String>()
    private lateinit var viewPager: ViewPager2
    private lateinit var tabLayout: TabLayout
    override fun onPermissionsChanged(permissions: Map<String, Boolean>) {

    }
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewTaskBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        tabLayout = findViewById(R.id.tab_layout)
        viewPager = binding.viewPager
        loadTitles()
        setUpViewPagerWithTabLayout()
        binding.titleBarTv.text = "#${intent.getStringExtra("id")}"
    }

    private fun setUpViewPagerWithTabLayout() {
        val pagerAdapter = ViewTasksPagerAdapter(this, listOfTitles, intent)
        viewPager.adapter = pagerAdapter
        addTabLayoutMediator()
    }

    private fun addTabLayoutMediator() {
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.task_info))
        listOfTitles.add(getString(R.string.attachments))
        listOfTitles.add(getString(R.string.comments))
        listOfTitles.add(getString(R.string.checklist_items))
    }
}