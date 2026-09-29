package com.divesh.perfex.sales.domain.adapters

import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.sales.presentation.invoices.presentation.manage.ManageInvoicesFragment
import com.divesh.perfex.sales.presentation.proposals.manage.ManageProposalsFragment

class SalesPagerAdapter(fa: FragmentActivity, private val listOfTitle: List<String>) :
    FragmentStateAdapter(fa) {
    override fun getItemCount(): Int = listOfTitle.size

    override fun createFragment(position: Int): Fragment {
        when (position) {
            0 -> return ManageProposalsFragment()
            1 -> return ManageInvoicesFragment()
            2 -> return ManageProposalsFragment()
            3 -> return ManageProposalsFragment()
            4 -> return ManageProposalsFragment()
            5 -> return ManageProposalsFragment()
        }
        return ManageProposalsFragment()
    }
}