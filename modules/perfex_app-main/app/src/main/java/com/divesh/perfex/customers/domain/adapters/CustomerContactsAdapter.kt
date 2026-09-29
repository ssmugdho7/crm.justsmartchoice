package com.divesh.perfex.customers.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.customers.domain.interfaces.CustomerContactsInterface
import com.divesh.perfex.customers.domain.models.CustomerResponseModel
import com.divesh.perfex.databinding.CustomerContactRowBinding

class CustomerContactsAdapter(private var records: ArrayList<CustomerResponseModel.Data.Contact>, private val listener: CustomerContactsInterface) :
    RecyclerView.Adapter<CustomerContactsAdapter.RecordsViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<CustomerResponseModel.Data.Contact>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): RecordsViewHolder {
        val binding = CustomerContactRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return RecordsViewHolder(binding)
    }

    class RecordsViewHolder(val binding: CustomerContactRowBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onBindViewHolder(holder: RecordsViewHolder, position: Int) {
        with(holder) {
            with(records[position]) {
                binding.sendMail.setOnClickListener {
                    listener.sendMail(email)
                }
                binding.activeSwitch.setOnClickListener {
                    listener.activeStatusChanged(binding.activeSwitch.isChecked, id)
                }
                binding.customerEdit.setOnClickListener {
                    listener.editContact(this)
                }
                binding.customerDelete.setOnClickListener {
                    listener.deleteContact(id, position)
                }
                binding.dialCall.setOnClickListener { 
                    listener.dialCall(phonenumber)
                }
                val contactPersonName = if(firstname != null){
                    if(lastname != null){
                        "$firstname $lastname"
                    }else{
                        firstname
                    }
                }else{
                    ""
                }
                binding.companyName.text = contactPersonName
                binding.email.text = email
                binding.phone.text = phonenumber
                binding.position.text = title
                binding.customerImage.text = firstname?.first()?.uppercase()
                binding.activeSwitch.isChecked = active == 1
            }
        }
    }
}