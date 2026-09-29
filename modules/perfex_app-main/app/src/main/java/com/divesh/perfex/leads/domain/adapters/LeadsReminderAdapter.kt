package com.divesh.perfex.leads.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.LeadsReminderRowBinding
import com.divesh.perfex.leads.domain.interfaces.LeadReminderInterface
import com.divesh.perfex.leads.domain.models.RemindersResponseModel

class LeadsReminderAdapter (private var list: ArrayList<RemindersResponseModel.Reminder>, private val listener: LeadReminderInterface): RecyclerView.Adapter<LeadsReminderAdapter.UserViewHolder>() {
    fun updateList(newUsers: List<RemindersResponseModel.Reminder>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int) : UserViewHolder {
        val binding = LeadsReminderRowBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return UserViewHolder(binding)
    }

    override fun getItemCount() = list.size

    override fun onBindViewHolder(holder: UserViewHolder, position: Int) {
        holder.bind(list[position], position)
    }

    inner class UserViewHolder(private val binding: LeadsReminderRowBinding): RecyclerView.ViewHolder(binding.root) {
        fun bind(list: RemindersResponseModel.Reminder, position: Int) {
            binding.name.text = binding.root.context.getString(R.string.start_date_and_end_date , list.firstname, list.lastname)
            binding.date.text = list.date
            binding.description.text = list.description
            if(list.isnotified == 1)
                binding.notified.text = binding.root.context.getString(R.string.notified , "Yes")
            else
                binding.notified.text = binding.root.context.getString(R.string.notified, "No")

            binding.edit.setOnClickListener{
                listener.editReminder(list)
            }
            binding.delete.setOnClickListener{
                listener.deleteReminder(list.id, position)
            }
        }
    }
}