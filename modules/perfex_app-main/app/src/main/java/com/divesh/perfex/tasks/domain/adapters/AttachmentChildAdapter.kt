package com.divesh.perfex.tasks.domain.adapters

import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.ImageView
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.databinding.AttachmentChildRowBinding
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import com.stfalcon.imageviewer.StfalconImageViewer

class AttachmentChildAdapter(
    private var records: List<ViewTaskResponseModel.Task.Comment.Attachment>,
    private var taskId: String,
) : RecyclerView.Adapter<AttachmentChildAdapter.MyViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<ViewTaskResponseModel.Task.Comment.Attachment>){
        records = newUsers
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): AttachmentChildAdapter.MyViewHolder {
        val binding = AttachmentChildRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return MyViewHolder(binding)
    }

    override fun onBindViewHolder(holder: AttachmentChildAdapter.MyViewHolder, position: Int) {
        holder.bind(records[position])
    }

    inner class MyViewHolder(private val binding: AttachmentChildRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: ViewTaskResponseModel.Task.Comment.Attachment) {
            loadImage(binding.image, record)
            binding.image.setOnClickListener {
                StfalconImageViewer.Builder(binding.root.context, records, ::loadImage)
                    .withStartPosition(layoutPosition)
                    .withTransitionFrom(binding.image)
                    .show()
            }
        }
    }

    private fun loadImage(imageView: ImageView, record: ViewTaskResponseModel.Task.Comment.Attachment) {
        if (record.file_name.endsWith("png") || record.file_name.endsWith("jpg") || record.file_name.endsWith("jpeg")) {
            imageView.load("${Constants.BASE_URL}/download/preview_image?path=uploads/tasks/${taskId}/${record.file_name}&type=${record.filetype}") {
                crossfade(true)
                placeholder(R.drawable.loading_image)
            }
        } else {
            imageView.setImageDrawable(ContextCompat.getDrawable(imageView.rootView.context, R.drawable.file_icon))
        }
    }
}