package com.divesh.perfex.tasks.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.ImageView
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.databinding.AttachmentRowBinding
import com.divesh.perfex.tasks.domain.interfaces.AttachmentInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import com.stfalcon.imageviewer.StfalconImageViewer
class AttachmentsAdapter(
    private var records: ArrayList<ViewTaskResponseModel.Task.Attachment>,
    private val listener: AttachmentInterface
) : RecyclerView.Adapter<AttachmentsAdapter.MyViewHolder>() {
    private var taskId: String = "0"
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ViewTaskResponseModel.Task.Attachment>, taskId: String) {
        records.clear()
        records.addAll(newUsers)
        this.taskId = taskId
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): MyViewHolder {
        val binding = AttachmentRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    inner class MyViewHolder(private val binding: AttachmentRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ViewTaskResponseModel.Task.Attachment) {
            binding.fileName.text = record.file_name
            binding.dateTime.text = record.dateadded
            try {
                loadImage(binding.image, record)
                binding.image.setOnClickListener {
                    StfalconImageViewer.Builder(binding.root.context, records, ::loadImage)
                        .withStartPosition(layoutPosition)
                        .withTransitionFrom(binding.image)
                        .show()
                    listener.onAttachmentClicked(record)
                }
            } catch (exception: Exception) {
                exception.printStackTrace()
            }
            binding.iconClose.setOnClickListener { listener.onAttachmentRemoved(record) }
        }
    }

    private fun loadImage(imageView: ImageView, record: ViewTaskResponseModel.Task.Attachment) {
        if (record.file_name.endsWith("png") ||record.file_name.endsWith("jpg") || record.file_name.endsWith("jpeg")) {
            imageView.load("${Constants.BASE_URL}/download/preview_image?path=uploads/tasks/${taskId}/${record.file_name}&type=${record.filetype}") {
                crossfade(true)
                placeholder(R.drawable.loading_image)
            }
        } else {
            imageView.background =
                ContextCompat.getDrawable(imageView.rootView.context, R.drawable.file_icon)
        }
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        holder.bind(records[position])
    }
}