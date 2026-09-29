package com.divesh.perfex.notifications.domain.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.divesh.perfex.R
import com.divesh.perfex.databinding.NotificationsRowBinding
import com.divesh.perfex.notifications.domain.interfaces.NotificationInterface
import com.divesh.perfex.notifications.domain.models.NotificationsResponseModel

class ManageNotificationsAdapter(private var records: ArrayList<NotificationsResponseModel.Notification>, private val listener: NotificationInterface): RecyclerView.Adapter<ManageNotificationsAdapter.ViewHolder>() {
    override fun getItemViewType(position: Int): Int {
        return position
    }

    override fun getItemCount() = records.size

    override fun getItemId(position: Int): Long {
        return position.toLong()
    }

    fun setRecords(newUsers: List<NotificationsResponseModel.Notification>) {
        records.clear()
        records.addAll(newUsers)
        notifyDataSetChanged()
    }
    override fun onCreateViewHolder(parent: ViewGroup, p1: Int): ViewHolder {
        val binding = NotificationsRowBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    inner class ViewHolder(private val binding: NotificationsRowBinding) :
        RecyclerView.ViewHolder(binding.root) {
        fun bind(record: NotificationsResponseModel.Notification) {
            if(record.from_fullname != null) {
                binding.description.text = "${record.from_fullname} ${record.description}"
            }else{
                binding.description.text = record.description
            }
            binding.time.text = record.date
            binding.staffImage.load(record.profile_image){
                crossfade(true)
                placeholder(R.drawable.loading_image)
            }
            if(record.isread == 1){
                binding.mainCardView.backgroundTintList = ContextCompat.getColorStateList(binding.root.context, R.color.GrayGoose)
                binding.markAsRead.visibility = View.GONE
            }else{
                binding.mainCardView.backgroundTintList = ContextCompat.getColorStateList(binding.root.context, R.color.white)
                binding.markAsRead.visibility = View.VISIBLE
                binding.markAsRead.setOnClickListener {
                    listener.markAsRead(record)
                }
            }
        }
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(records[position])
    }
}