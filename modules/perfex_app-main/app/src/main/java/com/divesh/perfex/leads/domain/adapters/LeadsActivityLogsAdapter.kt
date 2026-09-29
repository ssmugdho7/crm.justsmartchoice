package com.divesh.perfex.leads.domain.adapters

import android.content.res.Resources
import android.util.TypedValue
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.databinding.LeadActivityLogRowBinding
import com.divesh.perfex.leads.domain.models.LeadActivityLogs
import com.lriccardo.timelineview.TimelineAdapter
import com.lriccardo.timelineview.TimelineView

class LeadsActivityLogsAdapter(private var list: ArrayList<LeadActivityLogs.Log>) :
    RecyclerView.Adapter<LeadsActivityLogsAdapter.UserViewHolder>(), TimelineAdapter {
    fun updateList(newUsers: List<LeadActivityLogs.Log>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): UserViewHolder {
        val binding = LeadActivityLogRowBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return UserViewHolder(binding)
    }

    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    override fun getItemCount() = list.size

    override fun onBindViewHolder(holder: UserViewHolder, position: Int) {
        holder.bind(list[position])
    }

    override fun getIndicatorStyle(position: Int): TimelineView.IndicatorStyle {
        return TimelineView.IndicatorStyle.Checked
    }


    override fun getLinePadding(position: Int): Float? {
        if (position > 1)
            return TypedValue.applyDimension(
                TypedValue.COMPLEX_UNIT_DIP,
                4f,
                Resources.getSystem().displayMetrics
            )
        return super.getLinePadding(position)
    }

    class UserViewHolder(private val binding: LeadActivityLogRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(list: LeadActivityLogs.Log) {
            binding.description.text = list.additional_data
            binding.date.text = list.date
            binding.dateAgo.text = list.time_ago.toString().uppercase()
        }
    }
}