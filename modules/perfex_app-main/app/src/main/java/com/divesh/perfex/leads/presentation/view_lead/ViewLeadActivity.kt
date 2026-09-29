package com.divesh.perfex.leads.presentation.view_lead

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.ImageView
import android.widget.Toast
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityViewLeadBinding
import com.divesh.perfex.leads.domain.adapters.ViewLeadsPagerAdapter
import com.divesh.perfex.leads.presentation.view_lead.convert.ConvertToCustomer
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint class ViewLeadActivity : AppCompatActivity() {
    private lateinit var binding: ActivityViewLeadBinding
    private lateinit var viewPager: ViewPager2
    private val listOfTitles = arrayListOf<String>()
    private lateinit var tabLayout: TabLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewLeadBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        tabLayout = binding.tabLayout
        viewPager = binding.viewPager
        setUpViewPagerWithTabLayout()

        val leadId = intent.getIntExtra("id", 0)
        val name = intent.getStringExtra("name")
        binding.toolbar.findViewById<androidx.appcompat.widget.AppCompatTextView>(R.id.toolbarText).text = getString(
            R.string.task_title,
            leadId.toString(),
            name
        )
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarConvert).setOnClickListener {
            if (!intent.getStringExtra("date_converted").isNullOrEmpty()) {
                Extensions().showMessage(binding.root, "Already a client")
            } else {
                val newIntent = Intent(this, ConvertToCustomer::class.java)
                newIntent.putExtra("name", intent.getStringExtra("name"))
                newIntent.putExtra("title", intent.getStringExtra("title"))
                newIntent.putExtra("email", intent.getStringExtra("email"))
                newIntent.putExtra("company", intent.getStringExtra("company"))
                newIntent.putExtra("phonenumber", intent.getStringExtra("phonenumber"))
                newIntent.putExtra("website", intent.getStringExtra("website"))
                newIntent.putExtra("address", intent.getStringExtra("address"))
                newIntent.putExtra("city", intent.getStringExtra("city"))
                newIntent.putExtra("state", intent.getStringExtra("state"))
                newIntent.putExtra("countryName", intent.getStringExtra("countryName"))
                newIntent.putExtra("zip", intent.getStringExtra("zip"))
                newIntent.putExtra("id", intent.getIntExtra("id", 0))
                startActivity(newIntent)
            }
        }

        if(!intent.getStringExtra("date_converted").isNullOrEmpty()){
            binding.toolbar.findViewById<Button>(R.id.toolbarConvert).visibility = View.GONE
        }else{
            binding.toolbar.findViewById<Button>(R.id.toolbarConvert).visibility = View.VISIBLE
        }
    }

    private fun setUpViewPagerWithTabLayout() {
        loadTitles()
        val pagerAdapter = ViewLeadsPagerAdapter(this, listOfTitles.size, intent)
        viewPager.adapter = pagerAdapter
        viewPager.currentItem = 0
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_profile))
        listOfTitles.add(getString(R.string.tab_task))
        listOfTitles.add(getString(R.string.tab_reminders))
        listOfTitles.add(getString(R.string.tab_notes))
        listOfTitles.add(getString(R.string.tab_activity_log))
    }
}