package com.divesh.perfex.tasks.domain.adapters

import android.util.Log
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants.Companion.universalLogTag
import com.divesh.perfex.databinding.ChecklistItemRowBinding
import com.divesh.perfex.tasks.domain.interfaces.ChecklistItemInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel

class CheckListItemsAdapter(private var records: ArrayList<ViewTaskResponseModel.Task.ChecklistItems>, private val listener: ChecklistItemInterface) : RecyclerView.Adapter<CheckListItemsAdapter.MyViewHolder>() {
    private var taskId: String = "0"
    private var staff: List<ViewTaskResponseModel.Staff>? = null
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(checklistItems: List<ViewTaskResponseModel.Task.ChecklistItems>, staff: List<ViewTaskResponseModel.Staff>?, taskId: String) {
        records.clear()
        records.addAll(checklistItems)
        this.taskId = taskId
        this.staff = staff
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = ChecklistItemRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: ChecklistItemRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ViewTaskResponseModel.Task.ChecklistItems) {
            binding.checklistDescription.text = record.description
            if(record.finished_from != 0 && record.finished == 1){
                binding.completedBy.text = binding.root.context.getString(R.string.completed_by, getStaffNameById(record.finished_from))
                binding.checkbox.isChecked = true
            }
            if(record.assigned.toString().isNotBlank() && record.assigned.toString() != "0" && record.assigned.toString() != "null"){
                binding.assignedTo.text =  binding.root.context.getString(R.string.assigned_to, getStaffNameById(record.assigned.toString().toInt()))
            }
            binding.saveAsTemplate.setOnClickListener { listener.onSaveAsTemplate(record) }
            binding.assignStaff.setOnClickListener { listener.onAssignStaff(record) }
            binding.edit.setOnClickListener { listener.onEdit(record) }
            binding.delete.setOnClickListener { listener.onDelete(record) }
            binding.checkbox.setOnCheckedChangeListener { _, b -> listener.onCheckboxChecked(record, b) }
        }
    }

    private fun getStaffNameById(assignedStaffId: Int): String {

        var staffName = ""
        if(this.staff?.isNotEmpty() == true){
            this.staff?.forEach {
                if(it.staffid == assignedStaffId){
                    staffName = "${it.firstname} ${it.lastname}"
                    Log.d(universalLogTag, "getStaffNameById : matched")
                    return@forEach
                }
            }
        }else{
            Log.d(universalLogTag, "getStaffNameById : empty staff")
        }
        return staffName
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}