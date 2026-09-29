package com.divesh.perfex.sales.domain.adapters.invoices

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.databinding.InvoiceItemRowBinding
import com.divesh.perfex.sales.domain.models.invoices.ViewInvoiceResponseModel

class InvoiceInfoItemsAdapter(private var records: ArrayList<ViewInvoiceResponseModel.Data.Invoice.Item>):
    RecyclerView.Adapter<InvoiceInfoItemsAdapter.MyViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ViewInvoiceResponseModel.Data.Invoice.Item>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): InvoiceInfoItemsAdapter.MyViewHolder {
        val binding = InvoiceItemRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: InvoiceItemRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ViewInvoiceResponseModel.Data.Invoice.Item) {
            binding.item.text = record.long_description.parseAsHtml()
            binding.rate.text = record.rate.toString()
            binding.quantity.text = record.qty.toString()
            binding.amount.text = (record.rate * record.qty).toString()
        }
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}