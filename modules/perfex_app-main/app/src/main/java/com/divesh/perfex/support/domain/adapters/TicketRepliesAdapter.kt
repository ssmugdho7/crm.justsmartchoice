package com.divesh.perfex.support.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.databinding.TicketReplyRowBinding
import com.divesh.perfex.support.domain.models.TicketRepliesResponseModel

class TicketRepliesAdapter(private var list: ArrayList<TicketRepliesResponseModel.Reply>): RecyclerView.Adapter<TicketRepliesAdapter.UserViewHolder>() {
    fun updateList(newUsers: List<TicketRepliesResponseModel.Reply>) {
        list.clear()
        list.addAll(newUsers)
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, p1: Int) : TicketRepliesAdapter.UserViewHolder {
        val binding = TicketReplyRowBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return UserViewHolder(binding)
    }

    override fun getItemCount() = list.size

    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    override fun onBindViewHolder(holder: TicketRepliesAdapter.UserViewHolder, position: Int) {
        holder.bind(list[position])
    }

    inner class UserViewHolder(private val binding: TicketReplyRowBinding): RecyclerView.ViewHolder(binding.root) {
        fun bind(record: TicketRepliesResponseModel.Reply) {
            binding.contactName.text = record.name
            binding.replyDate.text = record.date
            if(record.message != null)
                binding.message.text = record.message.parseAsHtml()
            if(record.fileName != null){
                binding.imageView.visibility = View.VISIBLE
                val uri = "${Constants.BASE_URL}/download/preview_image?path=uploads/ticket_attachments/${record.ticketid}/${record.fileName}&type=${record.fileType}"
                binding.imageView.load(uri){
                    crossfade(true)
                    placeholder(R.drawable.loading_image)
                }
            }else{
                binding.imageView.visibility = View.GONE
            }
        }
    }
}