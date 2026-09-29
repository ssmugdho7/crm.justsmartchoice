package com.divesh.perfex.tasks.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.divesh.perfex.databinding.TaskCommentRowBinding
import com.divesh.perfex.tasks.domain.interfaces.CommentsInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel


class CommentsAdapter(
    private var records: ArrayList<ViewTaskResponseModel.Task.Comment>,
    private val listener: CommentsInterface
) : RecyclerView.Adapter<CommentsAdapter.MyViewHolder>() {
    private var taskId: String = "0"
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ViewTaskResponseModel.Task.Comment>, taskId: String) {
        records.clear()
        records.addAll(newUsers)
        this.taskId = taskId
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = TaskCommentRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: TaskCommentRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ViewTaskResponseModel.Task.Comment) {
            binding.comment.text = record.content.replace("[task_attachment]", "").parseAsHtml()
            setupAttachments(record, binding)
            binding.iconClose.setOnClickListener { listener.removeComment(record) }
            binding.iconEdit.setOnClickListener { listener.editComment(record) }
        }

        private fun setupAttachments(
            record: ViewTaskResponseModel.Task.Comment,
            binding: TaskCommentRowBinding
        ) {
            if (record.attachments != null) {
                if (record.attachments.isNotEmpty()) {
                    val attachmentChildAdapter = AttachmentChildAdapter(record.attachments, taskId)
                    binding.attachmentRecyclerView.visibility = View.VISIBLE
                    binding.attachmentRecyclerView.apply {
                        layoutManager = LinearLayoutManager(binding.root.context, LinearLayoutManager.HORIZONTAL, false)
                        adapter = attachmentChildAdapter
                    }
                    attachmentChildAdapter.setRecords(record.attachments)
                }else{
                    binding.attachmentRecyclerView.visibility = View.GONE
                }
            }else{
                binding.attachmentRecyclerView.visibility = View.GONE
            }
        }
    }
    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}