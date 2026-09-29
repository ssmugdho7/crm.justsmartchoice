package com.divesh.perfex.sales.domain.adapters.invoices

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.InvoiceRowBinding
import com.divesh.perfex.sales.domain.interfaces.ManageInvoiceInterface
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse

class ManageInvoicesAdapter (
    private var records: ArrayList<InvoiceResponse.Invoice>,
    private val listener: ManageInvoiceInterface
) :
    RecyclerView.Adapter<ManageInvoicesAdapter.MyViewHolder>() {
    private val invoiceStatus = arrayListOf("UNPAID", "PAID", "PARTIALLY", "OVERDUE", "CANCELLED", "DRAFT")
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<InvoiceResponse.Invoice>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = InvoiceRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: InvoiceRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: InvoiceResponse.Invoice) {
            val statusValue = invoiceStatus[record.status+1]
            binding.clientRibbon.ribbon.text = statusValue
            binding.status.text = statusValue
            binding.clientRibbon.ribbon.setBackgroundColor(ContextCompat.getColor(binding.root.context, R.color.CloverGreen))

            binding.total.text = record.fancyTotal.toString()
            binding.totalTax.text = record.fancyTax.toString()
            binding.customer.text = record.companyName
            binding.date.text = record.fancyDueDate
            binding.id.text = record.invoiceNumber
            binding.mainCardView.setOnClickListener {
                listener.onInvoiceDetailsBtnClicked(record)
            }
            binding.recordView.setOnClickListener {
                listener.onInvoiceViewed(record)
            }
            binding.recordEdit.setOnClickListener {
                listener.onInvoiceEdit(record)
            }
        }
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}