package com.divesh.perfex.tasks.presentation.manage

import android.content.Intent
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.FrameLayout
import android.widget.ProgressBar
import android.widget.Toast
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.fragments.CoreFragment
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentLeadsTaskBinding
import com.divesh.perfex.leads.domain.adapters.TasksAdapter
import com.divesh.perfex.leads.domain.interfaces.TasksInterface
import com.divesh.perfex.leads.domain.models.Task
import com.divesh.perfex.tasks.presentation.add.AddTaskActivity
import com.divesh.perfex.tasks.presentation.edit.EditTaskActivity
import com.divesh.perfex.tasks.presentation.view.ViewTaskActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class TasksFragment : CoreFragment(), TasksInterface {
    private var _binding: FragmentLeadsTaskBinding? = null
    private val binding get() = _binding!!
    private val viewModel: TaskViewModel by viewModels()
    private val listAdapter = TasksAdapter(arrayListOf(), this)
    private var relId: Int = 0
    private var relType: String? = ""
    private lateinit var progressBar: ProgressBar
    private lateinit var lottieAnimation: LottieAnimationView
    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentLeadsTaskBinding.inflate(inflater, container, false)
        val view = binding.root
        registerInternetConnectionReceiver()
        progressBar = binding.progressBar

        lottieAnimation = view.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }
        binding.swipeRefresh.setOnRefreshListener {
            relType?.let { viewModel.refresh(relId, it) }
            binding.swipeRefresh.isRefreshing = false
        }
        observeViewModel(view)
        binding.addTask.setOnClickListener {
            startActivity(Intent(activity, AddTaskActivity::class.java).apply {
                putExtra("relId", relId)
                putExtra("relType", relType)
            })
        }
        binding.searchBar.addTextChangedListener(object : TextWatcher {
            override fun afterTextChanged(s: Editable?) {
            }

            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {
            }

            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {
                viewModel.filterRecords(s.toString())
            }
        })
        val relIdFromArg = arguments?.getInt("relId", 0)
        val relTypeFromArg = arguments?.getString("relType", "")
        if (relIdFromArg != null && relTypeFromArg != null) {
            relId = relIdFromArg
            relType = relTypeFromArg
        }
        if (hasInternet)
            relType?.let { viewModel.refresh(relId, it) }
        return view
    }

    private fun observeViewModel(view: FrameLayout) {
        viewModel.currentRecords.observe(viewLifecycleOwner) { users ->
            users?.let {
                listAdapter.updateList(it)
            }
        }

        viewModel.totalCount.observe(viewLifecycleOwner) { totalCount ->
            binding.totalRecords.text = getString(R.string.total_count, totalCount)
            if (totalCount != null) {
                val lottieAnimation = view.findViewById<LottieAnimationView>(R.id.noDataFound)
                if (totalCount > 0) {
                    binding.swipeRefresh.visibility = View.VISIBLE
                    lottieAnimation.visibility = View.GONE
                } else {
                    lottieAnimation.visibility = View.VISIBLE
                    binding.swipeRefresh.visibility = View.GONE
                }
            }
        }

        viewModel.filteredCount.observe(viewLifecycleOwner) { filteredCount ->
            binding.filteredRecords.text = getString(R.string.total_filtered, filteredCount)
        }

        viewModel.loadError.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null && isError != "null") {
                progressBar.visibility = View.GONE
                context?.let { Toast.makeText(it, isError.toString(), Toast.LENGTH_SHORT).show() }
            }
        }

        viewModel.removedTaskAtPosition.observe(viewLifecycleOwner) {
            listAdapter.notifyItemRemoved(it)
            relType?.let { it1 -> viewModel.refresh(relId, it1) }
        }

        viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }

        viewModel.taskTimerUpdated.observe(viewLifecycleOwner) {
            if(it != null){
                if(it){
                    relType?.let { it1 -> viewModel.refresh(relId, it1) }
                }
            }
        }
    }

    override fun onResume() {
        super.onResume()
        relType?.let { viewModel.refresh(relId, it) }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onEditTaskClicked(task: Task) {
        val intent = Intent(context, EditTaskActivity::class.java)
        setIntent(intent, task)
        startActivity(intent)
    }

    override fun onViewTaskClicked(task: Task) {
        val intent = Intent(context, ViewTaskActivity::class.java)
        setIntent(intent, task)
        startActivity(intent)
    }

    private fun setIntent(intent: Intent, list: Task) {
        intent.putExtra("STATUS", list.STATUS.toString())
        intent.putExtra("assignees", list.assignees)
        intent.putExtra("assignees_ids", list.assignees_ids)
        intent.putExtra("billed", list.billed)
        intent.putExtra("billable", list.billable)
        intent.putExtra("current_user_is_assigned", list.current_user_is_assigned.toString())
        intent.putExtra("current_user_is_creator", list.current_user_is_creator.toString())
        intent.putExtra("cycles", list.cycles.toString())
        intent.putExtra("duedate", list.duedate)
        intent.putExtra("hourly_rate", list.hourly_rate.toString())
        intent.putExtra("id", list.id.toString())
        intent.putExtra("is_assigned", list.is_assigned.toString())
        intent.putExtra(
            "not_finished_timer_by_current_staff",
            list.not_finished_timer_by_current_staff.toString()
        )
        intent.putExtra("priority", list.priority.toString())
        intent.putExtra("recurring", list.recurring.toString())
        intent.putExtra("recurring_type", list.recurring_type)
        intent.putExtra("custom_recurring", list.custom_recurring.toString())
        intent.putExtra("rel_id", list.rel_id.toString())
        intent.putExtra("rel_name", list.rel_name)
        intent.putExtra("rel_type", list.rel_type)
        intent.putExtra("repeat_every", list.repeat_every.toString())
        intent.putExtra("startdate", list.startdate)
        intent.putExtra("tags", list.tags)
        intent.putExtra("task_name", list.task_name)
        intent.putExtra("total_cycles", list.total_cycles.toString())
        intent.putExtra("description", list.description)
        intent.putExtra("total_logged_time", list.total_logged_time)
        intent.putExtra("my_logged_time", list.my_logged_time)
    }

    override fun startOrStopTimer(taskId: Int, notFinishedTimerByCurrentStaff: Int) {
        if (hasInternet) {
            if (notFinishedTimerByCurrentStaff > 0) {
                viewModel.startOrStopTask(taskId, notFinishedTimerByCurrentStaff)
            } else {
                viewModel.startOrStopTask(taskId, 0)
            }
        }
    }

    override fun deleteTask(id: Int, position: Int) {
        if (hasInternet) {
            Extensions().simpleAlert(getString(R.string.confirm_deletion), requireContext()) { isConfirmed ->
                if (isConfirmed) {
                    viewModel.deleteTask(id, position)
                }
            }
        }
    }

    override fun updateTaskStatus(taskId: Int, selectedPosition: Int) {
        viewModel.updateTaskStatus(taskId, selectedPosition)
    }
}