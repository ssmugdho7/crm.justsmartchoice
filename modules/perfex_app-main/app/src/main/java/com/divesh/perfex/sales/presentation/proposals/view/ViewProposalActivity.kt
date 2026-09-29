package com.divesh.perfex.sales.presentation.proposals.view

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import androidx.viewpager2.widget.ViewPager2
import com.divesh.perfex.R
import com.divesh.perfex.databinding.ActivityViewProposalBinding
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel
import com.divesh.perfex.sales.presentation.proposals.view.proposal_discussion.ViewProposalItemDiscussionsFragment
import com.divesh.perfex.sales.presentation.proposals.view.proposal_items.ManageProposalItemsFragment
import com.google.android.material.tabs.TabLayout
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class ViewProposalActivity : AppCompatActivity() {
    private lateinit var binding: ActivityViewProposalBinding
    private val listOfTitles = arrayListOf<String>()
    private lateinit var viewPager: ViewPager2
    private lateinit var tabLayout: TabLayout
    private var proposal: ProposalsResponseModel.Proposal? = null
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewProposalBinding.inflate(layoutInflater)
        setContentView(binding.root)
        initViews()
    }

    private fun initViews() {
        proposal = intent.getParcelableExtra("proposal") as ProposalsResponseModel.Proposal?
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        tabLayout = findViewById(R.id.tab_layout)
        tabLayout.tabMode = TabLayout.MODE_FIXED
        viewPager = binding.viewPager
        loadTitles()
        setUpViewPagerWithTabLayout()
        binding.titleBarTv.text = "#${proposal?.id}"
    }

    private fun setUpViewPagerWithTabLayout() {
        val pagerAdapter = ViewPagerAdapter(this, listOfTitles, proposal)
        viewPager.adapter = pagerAdapter
        viewPager.currentItem = 0
        addTabLayoutMediator()
    }

    private fun addTabLayoutMediator() {
        TabLayoutMediator(tabLayout, viewPager) { tab: TabLayout.Tab, position: Int ->
            tab.text = listOfTitles[position]
        }.attach()
    }

    private fun loadTitles() {
        listOfTitles.add(getString(R.string.tab_items))
        listOfTitles.add(getString(R.string.tab_discussion))
    }

    class ViewPagerAdapter(fa: FragmentActivity, private val listOfTitle: List<String>, private val proposal: ProposalsResponseModel.Proposal?) :
        FragmentStateAdapter(fa) {
        override fun getItemCount(): Int = listOfTitle.size

        override fun createFragment(position: Int): Fragment {
            val bundle = Bundle()
            bundle.putParcelable("proposal", proposal)
            when (position) {
                0 -> return ManageProposalItemsFragment().apply { this.arguments = bundle }
                1 -> return ViewProposalItemDiscussionsFragment().apply { this.arguments = bundle }
            }
            return ManageProposalItemsFragment()
        }
    }
}