package com.divesh.perfex.support.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.TicketsRowBinding
import com.divesh.perfex.support.domain.models.SupportResponseModel
import com.divesh.perfex.support.domain.interfaces.SupportInterface

class SupportAdapter(private var list: ArrayList<SupportResponseModel.Ticket>, private val listener: SupportInterface): RecyclerView.Adapter<SupportAdapter.UserViewHolder>() {
    fun updateList(newUsers: List<SupportResponseModel.Ticket>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int) : UserViewHolder {
        val binding = TicketsRowBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return UserViewHolder(binding)
    }

    override fun getItemCount() = list.size

    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    override fun onBindViewHolder(holder: UserViewHolder, position: Int) {
        holder.bind(list[position], position)
    }

    inner class UserViewHolder(private val binding: TicketsRowBinding): RecyclerView.ViewHolder(binding.root){
        fun bind(record: SupportResponseModel.Ticket, position: Int) {
            binding.subject.text = record.subject
            binding.name.text = record.name
            binding.lastReply.text = record.lastreply
            if(record.departmentName?.isNotBlank() == true)
                binding.department.text = record.departmentName
            binding.priority.text = when(record.priority){
                1 -> binding.root.context.getString(R.string.low_priority)
                2 -> binding.root.context.getString(R.string.medium_priority)
                3 -> binding.root.context.getString(R.string.high_priority)
                4 -> binding.root.context.getString(R.string.urgent_priority)
                else -> {
                    binding.root.context.getString(R.string.not_available)
                }
            }
            binding.status.text = when(record.status){
                1 -> binding.root.context.getString(R.string.status_open)
                2 -> binding.root.context.getString(R.string.status_in_progress)
                3 -> binding.root.context.getString(R.string.status_answered)
                4 -> binding.root.context.getString(R.string.status_on_hold)
                5 -> binding.root.context.getString(R.string.closed)
                else -> {
                    binding.root.context.getString(R.string.not_available)
                }
            }
            binding.delete.setOnClickListener {
                listener.onDeleteBtnClicked(record.ticketid, position)
            }
            binding.edit.setOnClickListener {
                listener.onEditBtnClicked(record)
            }
            binding.viewPublicForm.setOnClickListener {
                listener.onViewPublicFormClicked(record)
            }
        }
    }
}