package com.divesh.perfex.dashboard.presentation

import android.content.Intent
import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.services.NotificationService
import com.divesh.perfex.databinding.FragmentDashboardBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class DashboardFragment : Fragment() {
    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!
    private val viewModel: DashboardViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        val view = binding.root
        initObservers()
        return view
    }

    private fun initObservers() {
        viewModel.currentRecords.observe(viewLifecycleOwner) {
            if (it != null) {
                context?.let { context ->
                    binding.activeCustomersStats.text = context.getString(
                        R.string.dashboard_stats,
                        it.customerData.activeCustomers.toString(),
                        it.customerData.totalCustomers.toString()
                    )
                    binding.taskNotFinishedStats.text = context.getString(
                        R.string.dashboard_stats,
                        it.tasksData.tasksNotFinished.toString(),
                        it.tasksData.totalTasks.toString()
                    )
                    binding.ticketsStats.text = context.getString(
                        R.string.dashboard_stats,
                        it.ticketsData.openTickets.toString(),
                        it.ticketsData.totalTickets.toString()
                    )
                    binding.convertedLeadsStats.text = context.getString(
                        R.string.dashboard_stats,
                        it.leadsData.convertedLeads.toString(),
                        it.leadsData.totalLeads.toString()
                    )

                    binding.activeContacts.text = it.contactsData.activeContacts.toString()
                    binding.inActiveContacts.text = it.contactsData.inActiveContacts.toString()
                    binding.highPriorityTickets.text = it.ticketsData.highPriorityTickets.toString()
                    binding.lowPriorityTickets.text = it.ticketsData.lowPriorityTickets.toString()
                    binding.mediumPriorityTickets.text =
                        it.ticketsData.mediumPriorityTickets.toString()
                    binding.ticketsWithoutContact.text =
                        it.ticketsData.ticketsWithoutContact.toString()

                    if (it.customerData.totalCustomers > 0)
                        binding.activeCustomersProgressBar.setProgress(
                            ((it.customerData.activeCustomers.toFloat() / it.customerData.totalCustomers.toFloat()) * 100).toInt(),
                            true
                        )

                    if (it.tasksData.totalTasks > 0)
                        binding.taskNotFinishedProgressBar.setProgress(
                            ((it.tasksData.tasksNotFinished.toFloat() / it.tasksData.totalTasks.toFloat()) * 100).toInt(),
                            true
                        )

                    if (it.ticketsData.totalTickets > 0)
                        binding.openTicketsProgessbar.setProgress(
                            ((it.ticketsData.openTickets.toFloat() / it.ticketsData.totalTickets.toFloat()) * 100).toInt(),
                            true
                        )

                    if (it.leadsData.totalLeads > 0)
                        binding.convertedLeadsProgressBar.setProgress(
                            ((it.leadsData.convertedLeads.toFloat() / it.leadsData.totalLeads.toFloat()) * 100).toInt(),
                            true
                        )

                    context.startService(Intent(context, NotificationService::class.java))
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}