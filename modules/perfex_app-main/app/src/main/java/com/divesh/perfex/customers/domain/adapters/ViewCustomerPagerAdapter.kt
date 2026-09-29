package com.divesh.perfex.customers.domain.adapters

import android.os.Bundle
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.customers.presentation.view_customer.tabs.contacts.manage.CustomerContactsFragment
import com.divesh.perfex.customers.presentation.view_customer.tabs.notes.manage.CustomerNotesFragment
import com.divesh.perfex.customers.presentation.view_customer.tabs.profile.ViewCustomerProfileFragment
import com.divesh.perfex.tasks.presentation.manage.TasksFragment

class ViewCustomerPagerAdapter(fa: FragmentActivity, private val tabCount: Int, private val userId: Int) :
    FragmentStateAdapter(fa) {
    override fun getItemCount(): Int = tabCount
    override fun createFragment(position: Int): Fragment {
        val bundle = Bundle()
        setBundle(bundle)
        when (position) {
            0 -> {
                return ViewCustomerProfileFragment().apply { this.arguments = bundle }
            }
            1 -> {
                return CustomerContactsFragment().apply { this.arguments = bundle }
            }
            2 -> {
                return CustomerNotesFragment().apply { this.arguments = bundle }
            }
//            3 -> {
//                return ViewCustomerProfileFragment().apply { this.arguments = bundle }
//            }
//            4 -> {
//                return ViewCustomerProfileFragment().apply { this.arguments = bundle }
//            }
            3 -> {
                val taskDataBundle = Bundle()
                taskDataBundle.putInt("relId", userId)
                taskDataBundle.putString("relType", "customer")
                return TasksFragment().apply {
                    this.arguments = taskDataBundle
                }
            }
            else -> {
                return ViewCustomerProfileFragment().apply { this.arguments = bundle }
            }
        }
    }

    private fun setBundle(bundle: Bundle) {
        bundle.putInt("userId", userId)
    }
}