package com.divesh.perfex.tasks.domain.adapters

import android.content.Intent
import android.os.Bundle
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.divesh.perfex.tasks.presentation.view.tabs.attachments.AttachmentsFragment
import com.divesh.perfex.tasks.presentation.view.tabs.checklist_items.manage.ChecklistItemsFragment
import com.divesh.perfex.tasks.presentation.view.tabs.comments.CommentsFragment
import com.divesh.perfex.tasks.presentation.view.tabs.task_info.TaskInfoFragment

class ViewTasksPagerAdapter(fa: FragmentActivity, private val listOfTitle: List<String>, private val intent: Intent) :
    FragmentStateAdapter(fa) {

    override fun getItemCount(): Int = listOfTitle.size

    override fun createFragment(position: Int): Fragment {
        val bundle = Bundle()
        setBundle(bundle, intent)
        when (position) {
            0 -> return TaskInfoFragment().apply { this.arguments = bundle }
            1 -> return AttachmentsFragment().apply { this.arguments = bundle }
            2 -> return CommentsFragment().apply { this.arguments = bundle }
            3 -> return ChecklistItemsFragment().apply { this.arguments = bundle }
        }
        return TaskInfoFragment()
    }

    private fun setBundle(bundle: Bundle, intent: Intent) {
        bundle.putString("STATUS", intent.getStringExtra("STATUS").toString())
        bundle.putString("assignees", intent.getStringExtra("assignees").toString())
        bundle.putString("assignees_ids", intent.getStringExtra("assignees_ids").toString())
        bundle.putString("billed", intent.getIntExtra("billed",0).toString())
        bundle.putString("billable", intent.getIntExtra("billable",0).toString())
        bundle.putString("current_user_is_assigned", intent.getStringExtra("current_user_is_assigned").toString())
        bundle.putString("current_user_is_creator", intent.getStringExtra("current_user_is_creator").toString())
        bundle.putString("cycles", intent.getStringExtra("cycles").toString())
        bundle.putString("duedate", intent.getStringExtra("duedate").toString())
        bundle.putString("hourly_rate", intent.getStringExtra("hourly_rate").toString())
        bundle.putString("id", intent.getStringExtra("id").toString())
        bundle.putString("is_assigned", intent.getStringExtra("is_assigned").toString())
        bundle.putString("not_finished_timer_by_current_staff",intent.getStringExtra("not_finished_timer_by_current_staff").toString())
        bundle.putString("priority", intent.getStringExtra("priority").toString())
        bundle.putString("recurring", intent.getStringExtra("recurring").toString())
        bundle.putString("recurring_type", intent.getStringExtra("recurring_type").toString())
        bundle.putString("custom_recurring", intent.getStringExtra("custom_recurring").toString())
        bundle.putString("rel_id", intent.getStringExtra("rel_id").toString())
        bundle.putString("rel_name", intent.getStringExtra("rel_name").toString())
        bundle.putString("rel_type", intent.getStringExtra("rel_type").toString())
        bundle.putString("repeat_every", intent.getStringExtra("repeat_every").toString())
        bundle.putString("startdate", intent.getStringExtra("startdate").toString())
        bundle.putString("tags", intent.getStringExtra("tags").toString())
        bundle.putString("task_name", intent.getStringExtra("task_name").toString())
        bundle.putString("total_cycles", intent.getStringExtra("total_cycles").toString())
        bundle.putString("description", intent.getStringExtra("description").toString())
        bundle.putString("description", intent.getStringExtra("description").toString())
        bundle.putString("my_logged_time", intent.getStringExtra("my_logged_time"))
        bundle.putString("total_logged_time", intent.getStringExtra("total_logged_time"))
    }
}