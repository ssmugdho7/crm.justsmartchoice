package com.divesh.perfex.core.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.customers.presentation.manage.CustomersFragment
import com.divesh.perfex.dashboard.presentation.DashboardFragment
import com.divesh.perfex.leads.presentation.manage.ManageLeadsFragment
import com.divesh.perfex.support.presentation.manage.TicketFragment
import com.divesh.perfex.tasks.presentation.manage.TasksFragment

class MainViewPagerAdapter(fa: FragmentActivity, private val listOfTitle: List<String>) :
    FragmentStateAdapter(fa) {
    override fun getItemCount(): Int = listOfTitle.size

    override fun createFragment(position: Int): Fragment {
        when (position) {
            0 -> return DashboardFragment()
            1 -> return CustomersFragment()
            2 -> return TasksFragment()
            3 -> return TicketFragment()
            4 -> return ManageLeadsFragment()
        }
        return CustomersFragment()
    }
}