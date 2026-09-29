package com.divesh.perfex.leads.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import androidx.core.content.ContextCompat
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.R
import com.divesh.perfex.databinding.LeadTaskRowBinding
import com.divesh.perfex.leads.domain.interfaces.TasksInterface
import com.divesh.perfex.leads.domain.models.Task

class TasksAdapter (private var taskList: ArrayList<Task>, private val tasksInterface: TasksInterface) :
    RecyclerView.Adapter<TasksAdapter.RecordsViewHolder>() {
    fun updateList(newUsers: List<Task>) {
        taskList.clear()
        taskList.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): RecordsViewHolder {
        val binding = LeadTaskRowBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return RecordsViewHolder(binding)
    }

    override fun getItemCount() = taskList.size

    override fun onBindViewHolder(holder: RecordsViewHolder, position: Int) {
        holder.bind(taskList[position], position)
    }

    inner class RecordsViewHolder(private val binding: LeadTaskRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(list: Task, position: Int) {
            val statusDropdownValues = arrayListOf(
                binding.root.context.getString(R.string.not_started),
                binding.root.context.getString(R.string.waiting_feedback),
                binding.root.context.getString(R.string.testing),
                binding.root.context.getString(R.string.in_progress),
                binding.root.context.getString(R.string.complete)
            )
            if(list.assignees != null)
                binding.assigned.text = list.assignees
            else
                binding.assigned.text =  binding.root.context.getString(R.string.data_with_title, "Assigned", "")
            binding.taskName.text = list.task_name
            if(list.description != null && list.description != "null")
                binding.description.text = list.description.parseAsHtml()
            if(list.startdate?.isNotBlank() == true)
                binding.startDate.text = binding.root.context.getString(R.string.data_with_title, "Start", list.startdate)
            else
                binding.startDate.text = binding.root.context.getString(R.string.data_with_title, "Start", "")
            
            if(list.duedate?.isNotBlank() == true)
                binding.dueDate.text = binding.root.context.getString(R.string.data_with_title, "Due", list.duedate)
            else
                binding.dueDate.text = binding.root.context.getString(R.string.data_with_title, "Due", "")
            binding.hourlyRate.text = binding.root.context.getString(R.string.data_with_title, "Rate", list.hourly_rate.toString())
            binding.totalCycles.text = binding.root.context.getString(R.string.data_with_title, "Cycle", list.total_cycles.toString())
//            binding.assigned.text = binding.root.context.getString(R.string.data_with_title, "Assigned", list.assignees)
            if(list.not_finished_timer_by_current_staff == 1){
                binding.isFinished.setBackgroundResource(R.drawable.background_rounded_corners_green_onion)
                binding.isFinished.text = binding.root.context.getString(R.string.finished)
            }else{
                binding.isFinished.setBackgroundResource(R.drawable.background_rounded_corners_blush_red)
                binding.isFinished.backgroundTintList = ContextCompat.getColorStateList(binding.root.context, R.color.ShockingOrange)
                binding.isFinished.text = binding.root.context.getString(R.string.not_finished)
            }
            if(list.billable == 1){
                binding.isBillable.setBackgroundResource(R.drawable.background_rounded_corners_blush_red)
                binding.isBillable.backgroundTintList = ContextCompat.getColorStateList(binding.root.context, R.color.ShockingOrange)
                binding.isBillable.text = binding.root.context.getString(R.string.billable)
            }else{
                binding.isBillable.setBackgroundResource(R.drawable.background_rounded_corners_green_onion)
                binding.isBillable.text = binding.root.context.getString(R.string.not_billable)
            }

            binding.status.setAdapter(
                ArrayAdapter(
                    binding.root.context, R.layout.list_item, statusDropdownValues
                )
            )
            if (list.recurring == 1) {
                binding.recurringTask.visibility = View.VISIBLE
                binding.recurringTask.text = binding.root.context.getString(R.string.recurring)
            } else {
                binding.recurringTask.visibility = View.GONE
                binding.recurringTask.text = ""
            }
            if (list.not_finished_timer_by_current_staff > 0) {
                binding.startTimer.text = binding.root.context.getString(R.string.stop_timer)
            } else {
                binding.startTimer.text = binding.root.context.getString(R.string.start_timer)
            }
            when (list.priority) {
                1 -> {
                    binding.priority.ribbon.text = binding.root.context.getString(R.string.low_priority)
                    binding.priority.ribbon.ribbonBackgroundColor = ContextCompat.getColor(binding.root.context, R.color.Gray)
                }
                2 -> {
                    binding.priority.ribbon.text = binding.root.context.getString(R.string.medium_priority)
                    binding.priority.ribbon.ribbonBackgroundColor = ContextCompat.getColor(binding.root.context, R.color.DarkTurquoise)
                }
                3 -> {
                    binding.priority.ribbon.text = binding.root.context.getString(R.string.high_priority)
                    binding.priority.ribbon.ribbonBackgroundColor = ContextCompat.getColor(binding.root.context, R.color.HarvestGold)
                }
                4 -> {
                    binding.priority.ribbon.text = binding.root.context.getString(R.string.urgent_priority)
                    binding.priority.ribbon.ribbonBackgroundColor = ContextCompat.getColor(binding.root.context, R.color.Red)
                }
            }
            when (list.STATUS) {
                1 -> {
                    binding.status.setText(
                        binding.root.context.getString(R.string.not_started), false
                    )
                }
                2 -> {
                    binding.status.setText(
                        binding.root.context.getString(R.string.waiting_feedback), false
                    )
                }
                3 -> {
                    binding.status.setText(binding.root.context.getString(R.string.testing), false)
                }
                4 -> {
                    binding.status.setText(
                        binding.root.context.getString(R.string.in_progress), false
                    )
                }
                5 -> {
                    binding.status.setText(
                        binding.root.context.getString(R.string.not_started), false
                    )
                }
            }

            binding.editTask.setOnClickListener {
                tasksInterface.onEditTaskClicked(list)
            }
            binding.startTimer.setOnClickListener {
                tasksInterface.startOrStopTimer(list.id, list.not_finished_timer_by_current_staff)
            }
            binding.deleteTask.setOnClickListener {
                tasksInterface.deleteTask(list.id, position)
            }
            binding.status.setOnItemClickListener { _, _, positionOfSelectedItem, _ ->
                tasksInterface.updateTaskStatus(list.id, (positionOfSelectedItem + 1))
            }
            binding.mainCardView.setOnClickListener{
                tasksInterface.onViewTaskClicked(list)
            }
        }
    }
}