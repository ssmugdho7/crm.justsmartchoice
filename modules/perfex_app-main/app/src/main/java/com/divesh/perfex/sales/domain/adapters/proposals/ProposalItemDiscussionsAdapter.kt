package com.divesh.perfex.sales.domain.adapters.proposals

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.databinding.ProposalItemDiscussionRowBinding
import com.divesh.perfex.sales.domain.models.proposals.ProposalCommentsResponseModel
import com.divesh.perfex.sales.domain.models.proposals.ProposalItemsResponseModel

class ProposalItemDiscussionsAdapter(private var records: ArrayList<ProposalCommentsResponseModel.Comment>) :
    RecyclerView.Adapter<ProposalItemDiscussionsAdapter.MyViewHolder>() {
    private var taxes: ArrayList<ProposalItemsResponseModel.Taxes> = arrayListOf()
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ProposalCommentsResponseModel.Comment>) {
        records.clear()
        records.addAll(newUsers)
        this.taxes.clear()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = ProposalItemDiscussionRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: ProposalItemDiscussionRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ProposalCommentsResponseModel.Comment) {
            binding.comment.text = record.content?.parseAsHtml()
            binding.title.text = record.staffName
            binding.date.text = record.dateadded
            binding.staffImage.load(record.staffImage){
                crossfade(true)
                placeholder(R.drawable.loading_image)
            }
        }
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}