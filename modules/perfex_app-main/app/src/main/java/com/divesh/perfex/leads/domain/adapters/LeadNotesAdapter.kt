package com.divesh.perfex.leads.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.LeadsNotesRowBinding
import com.divesh.perfex.leads.domain.interfaces.LeadNotesInterface
import com.divesh.perfex.leads.domain.models.LeadNotes


class LeadNotesAdapter(private var list: ArrayList<LeadNotes.Note>, private val listener: LeadNotesInterface): RecyclerView.Adapter<LeadNotesAdapter.UserViewHolder>() {
    fun updateList(newUsers: List<LeadNotes.Note>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int) : UserViewHolder {
        val binding = LeadsNotesRowBinding.inflate(LayoutInflater.from(parent.context), parent, false)
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

    inner class UserViewHolder(private val binding: LeadsNotesRowBinding): RecyclerView.ViewHolder(binding.root){
        fun bind(list: LeadNotes.Note, position: Int) {
            binding.name.text = binding.root.context.getString(R.string.start_date_and_end_date , list.firstname, list.lastname)
            binding.date.text = list.dateadded
            binding.description.text = list.description
            if(list.date_contacted != "") {
                binding.contactedDate.visibility = View.VISIBLE
                binding.contactedDate.text = list.date_contacted
            }else {
                binding.contactedDate.visibility = View.GONE
            }
            binding.edit.setOnClickListener{
                listener.editNote(list)
            }
            binding.delete.setOnClickListener{
                listener.deleteNote(list.id, position)
            }
        }
    }
}