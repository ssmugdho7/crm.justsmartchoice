package com.divesh.perfex.customers.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.customers.domain.interfaces.CustomerNotesInterface
import com.divesh.perfex.customers.domain.models.CustomerNotesResponseModel
import com.divesh.perfex.databinding.CustomerNoteRowBinding

class CustomerNotesAdapter (private var records: ArrayList<CustomerNotesResponseModel.Note>, private val listener: CustomerNotesInterface) :
    RecyclerView.Adapter<CustomerNotesAdapter.RecordsViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<CustomerNotesResponseModel.Note>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): RecordsViewHolder {
        val binding = CustomerNoteRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return RecordsViewHolder(binding)
    }

    class RecordsViewHolder(val binding: CustomerNoteRowBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onBindViewHolder(holder: RecordsViewHolder, position: Int) {
        with(holder) {
            with(records[position]) {
                binding.noteDelete.setOnClickListener {
                    listener.onNoteDeleted(id, position)
                }
                binding.noteEdit.setOnClickListener {
                    listener.onEditBtnClicked(this@with)
                }
                binding.noteDescription.text = description?.parseAsHtml()
            }
        }
    }
}