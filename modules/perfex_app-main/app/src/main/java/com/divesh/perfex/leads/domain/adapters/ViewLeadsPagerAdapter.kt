package com.divesh.perfex.leads.domain.adapters

import android.content.Intent
import android.os.Bundle
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.activity_log.LeadActivityLog
import com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.notes.LeadNotesFragment
import com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.profile.LeadsProfileFragment
import com.divesh.perfex.leads.presentation.view_lead.view_lead_tabs.reminders.manage.LeadRemindersFragment
import com.divesh.perfex.tasks.presentation.manage.TasksFragment

class ViewLeadsPagerAdapter(
    fa: FragmentActivity,
    private val tabCount: Int,
    private val intent: Intent
) :
    FragmentStateAdapter(fa) {
    override fun getItemCount(): Int = tabCount
    override fun createFragment(position: Int): Fragment {
        val bundle = Bundle()
        setBundle(bundle, intent)
        when (position) {
            0 -> {
                return LeadsProfileFragment().apply { this.arguments = bundle }
            }
            1 -> {
                val taskDataBundle = Bundle()
                taskDataBundle.putInt("relId", intent.getIntExtra("id", 0))
                taskDataBundle.putString("relType", "lead")
                return TasksFragment().apply { this.arguments = taskDataBundle }
            }
            2 -> {
                return LeadRemindersFragment().apply { this.arguments = bundle }
            }
            3 -> {
                return LeadNotesFragment().apply { this.arguments = bundle }
            }
            4 -> {
                return LeadActivityLog().apply { this.arguments = bundle }
            }
            else -> {
                return LeadsProfileFragment().apply { this.arguments = bundle }
            }
        }
    }

    private fun setBundle(bundle: Bundle, intent: Intent) {
        bundle.putString("id", intent.getIntExtra("id", 0).toString())
        bundle.putString("address", intent.getStringExtra("address").toString())
        bundle.putString("assigned", intent.getStringExtra("assigned").toString())
        bundle.putString("city", intent.getStringExtra("city").toString())
        bundle.putString("company", intent.getStringExtra("company").toString())
        bundle.putString("countryName", intent.getStringExtra("countryName").toString())
        bundle.putString("description", intent.getStringExtra("description").toString())
        bundle.putString("email", intent.getStringExtra("email").toString())
        bundle.putString("lead_value", intent.getStringExtra("lead_value").toString())
        bundle.putString("name", intent.getStringExtra("name").toString())
        bundle.putString("phonenumber", intent.getStringExtra("phonenumber").toString())
        bundle.putString("source", intent.getStringExtra("source").toString())
        bundle.putString("state", intent.getStringExtra("state").toString())
        bundle.putString("status", intent.getStringExtra("status").toString())
        bundle.putString("title", intent.getStringExtra("title").toString())
        bundle.putString("website", intent.getStringExtra("website").toString())
        bundle.putString("lastcontact", intent.getStringExtra("lastcontact").toString())
        bundle.putString("dateadded", intent.getStringExtra("dateadded").toString())
        bundle.putString("default_language", intent.getStringExtra("default_language").toString())
        bundle.putString("is_public", intent.getIntExtra("is_public", 0).toString())
        bundle.putString("zip", intent.getIntExtra("zip", 0).toString())
        bundle.putString("custom_fields", intent.getIntExtra("zip", 0).toString())
    }
}