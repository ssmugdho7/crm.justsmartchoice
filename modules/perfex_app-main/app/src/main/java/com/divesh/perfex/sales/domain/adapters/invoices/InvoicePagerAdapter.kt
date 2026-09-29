package com.divesh.perfex.sales.domain.adapters.invoices

import android.content.Intent
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.sales.presentation.invoices.presentation.details.tabs.info.InvoiceInfoFragment

class InvoicePagerAdapter(fa: FragmentActivity, private val listOfTitle: List<String>,  private val intent: Intent) :
    FragmentStateAdapter(fa) {
    override fun getItemCount(): Int = listOfTitle.size

    override fun createFragment(position: Int): Fragment {
        when (position) {
            0 -> return InvoiceInfoFragment.newInstance(intent.getParcelableExtra("invoice"))
        }
        return InvoiceInfoFragment.newInstance(intent.getParcelableExtra("invoice"))
    }
}