package com.divesh.perfex.sales.domain.adapters.proposals

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.ProposalItemRowBinding
import com.divesh.perfex.sales.domain.models.proposals.ProposalItemsResponseModel

class ManageProposalItemsAdapter(private var records: ArrayList<ProposalItemsResponseModel.Item>) :
    RecyclerView.Adapter<ManageProposalItemsAdapter.MyViewHolder>() {
    private var taxes: ArrayList<ProposalItemsResponseModel.Taxes> = arrayListOf()
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(
        newUsers: List<ProposalItemsResponseModel.Item>,
        taxes: List<ProposalItemsResponseModel.Taxes>
    ) {
        records.clear()
        records.addAll(newUsers)
        this.taxes.clear()
        this.taxes.addAll(taxes)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = ProposalItemRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: ProposalItemRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ProposalItemsResponseModel.Item) {
            binding.qty.text = "${record.qty} ${if(record.unit != null) record.unit else ""}"
            binding.rate.text = binding.root.context.getString(
                R.string.data_with_title,
                "Rate",
                record.rate.toString()
            )
            binding.itemDescription.text = record.long_description?.parseAsHtml()
            binding.itemName.text = record.description

            val tax = getItemTax(record.id)
            var amount = record.rate * record.qty
            if(tax > 0){
                amount = ((amount * tax) / 100).toInt()
            }
            binding.tax.text = binding.root.context.getString(R.string.data_with_title, "Tax", tax.toString())
            binding.amount.text =  binding.root.context.getString(R.string.data_with_title, "Total", amount.toString())
        }
    }

    private fun getItemTax(itemId: Int): Float {
        var tax = 0F
        if(this.taxes.isNotEmpty()){
            taxes.forEach {
                if(it.itemid == itemId){
                    tax += it.taxrate
                }
            }
        }
        return tax
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}