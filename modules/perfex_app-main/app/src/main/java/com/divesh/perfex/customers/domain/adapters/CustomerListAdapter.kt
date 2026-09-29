package com.divesh.perfex.customers.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.customers.domain.interfaces.CustomersListInterface
import com.divesh.perfex.customers.domain.models.CustomerListResponseModel
import com.divesh.perfex.databinding.SingleCustomerRowBinding

class CustomerListAdapter(
    private var list: ArrayList<CustomerListResponseModel.Customer>,
    private val listener: CustomersListInterface
) : RecyclerView.Adapter<CustomerListAdapter.UserViewHolder>() {
    fun updateList(newUsers: List<CustomerListResponseModel.Customer>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): UserViewHolder {
        val binding = SingleCustomerRowBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
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

    inner class UserViewHolder(private val binding: SingleCustomerRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: CustomerListResponseModel.Customer, position: Int) {
            binding.companyName.text = record.company
            binding.email.text = record.email
            binding.groups.text = record.customerGroups
            binding.contactPersonPhone.text = record.phonenumber
            val contactPersonName = if(record.firstname != null){
                if(record.lastname != null){
                    "${record.firstname} ${record.lastname}"
                }else{
                    record.firstname
                }
            }else{
                ""
            }
            binding.contactPersonName.text = contactPersonName
            if(record.company != null && record.company.isNotBlank()) {
                binding.customerImage.text = record.company.first().uppercase()
            }else{
                binding.customerImage.text = "N"
            }
            binding.activeSwitch.isChecked = record.tblclients_active == 1

            binding.dialCall.setOnClickListener {
                listener.dialCall(record.phonenumber)
            }

            binding.activeSwitch.setOnClickListener {
                listener.activeStatusChanged(binding.activeSwitch.isChecked, record.userid)
            }

            binding.sendMail.setOnClickListener {
                listener.sendMail(record.email)
            }

            binding.customerDelete.setOnClickListener {
                listener.deleteCustomer(record, position)
            }

            binding.customerEdit.setOnClickListener {
                listener.editCustomer(record)
            }

            binding.customerView.setOnClickListener {
                listener.viewCustomer(record)
            }
        }
    }
}