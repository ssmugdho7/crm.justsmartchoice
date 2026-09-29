package com.divesh.perfex.sales.domain.adapters.proposals

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.ProposalsRowBinding
import com.divesh.perfex.sales.domain.interfaces.ManageProposalsInterface
import com.divesh.perfex.sales.domain.models.proposals.ProposalsResponseModel

class ManageProposalsAdapter(
    private var records: ArrayList<ProposalsResponseModel.Proposal>,
    private val listener: ManageProposalsInterface
) :
    RecyclerView.Adapter<ManageProposalsAdapter.MyViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ProposalsResponseModel.Proposal>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = ProposalsRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: ProposalsRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ProposalsResponseModel.Proposal, position: Int) {
            when (record.status) {
                1 -> {
                    binding.clientRibbon.ribbon.text = "Open"
                    binding.status.text = "Open"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.Gray
                        )
                    )
                }
                2 -> {
                    binding.clientRibbon.ribbon.text = "Declined"
                    binding.status.text = "Declined"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.RedDirt
                        )
                    )
                }
                3 -> {
                    binding.clientRibbon.ribbon.text = "Accepted"
                    binding.status.text = "Accepted"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.CloverGreen
                        )
                    )
                }
                4 -> {
                    binding.clientRibbon.ribbon.text = "Sent"
                    binding.status.text = "Sent"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.Green
                        )
                    )
                }
                5 -> {
                    binding.clientRibbon.ribbon.text = "Revised"
                    binding.status.text = "Revised"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.OrangeSalmon
                        )
                    )
                }
                6 -> {
                    binding.clientRibbon.ribbon.text = "Draft"
                    binding.status.text = "Draft"
                    binding.clientRibbon.ribbon.setBackgroundColor(
                        ContextCompat.getColor(
                            binding.root.context,
                            R.color.Yellow
                        )
                    )
                }
            }
            binding.subject.text = record.subject
            binding.to.text = record.proposal_to
            binding.date.text = record.date
            binding.openTill.text = "Till:${record.open_till}"
            binding.total.text = "Total: ${record.total}"
            binding.id.text = "#${record.id}"
            binding.mainCardView.setOnClickListener {
                listener.onProposalClicked(record)
            }
        }
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position], position)
    }
}