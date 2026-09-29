package com.divesh.perfex.leads.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.databinding.LeadsRowBinding
import com.divesh.perfex.leads.domain.interfaces.ManageLeadsInterface
import com.divesh.perfex.leads.domain.models.Lead
import kotlin.collections.ArrayList

class ManageLeadsAdapter(private var records: ArrayList<Lead>, private val listener: ManageLeadsInterface) :
    RecyclerView.Adapter<ManageLeadsAdapter.LeadsViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<Lead>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): LeadsViewHolder {
        val binding = LeadsRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return LeadsViewHolder(binding)
    }

    inner class LeadsViewHolder(private val binding: LeadsRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: Lead, position: Int) {
            binding.companyName.text = record.company
            binding.contactPersonName.text = record.name
            binding.city.text = record.city
            binding.state.text = record.state
            binding.status.text = record.status
            binding.source.text = record.source
            if(!record.name.isNullOrBlank()){
                if(record.name.first().toString().isNotEmpty())
                    binding.customerImage.text = record.name.toString().first().uppercaseChar().toString()
                else
                    binding.customerImage.text = "N"
            }else{
                binding.customerImage.text = "N"
            }
            if (record.client_id > 0) {
                binding.clientRibbon.visibility = View.VISIBLE
            }else{
                binding.clientRibbon.visibility = View.GONE
            }
            binding.leadEdit.setOnClickListener {
                listener.onLeadEditClicked(record)
            }
            binding.mainCardView.setOnClickListener {
                listener.onLeadViewClicked(record)
            }
            binding.dialCall.setOnClickListener {
                listener.onDialCallClicked(record.phonenumber)
            }
            binding.sendMail.setOnClickListener {
                listener.onSendMailClicked(record.email)
            }
            binding.deleteLead.setOnClickListener {
                listener.onLeadDeleteClicked(record.id, position)
            }
        }
    }

    override fun onBindViewHolder(holder: LeadsViewHolder, position: Int) {
        holder.bind(records[position], position)
    }
}