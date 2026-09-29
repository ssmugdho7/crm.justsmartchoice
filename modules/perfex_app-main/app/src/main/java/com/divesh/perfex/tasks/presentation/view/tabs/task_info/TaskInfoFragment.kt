package com.divesh.perfex.tasks.presentation.view.tabs.task_info

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.text.parseAsHtml
import androidx.fragment.app.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.fragments.CoreFragment
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentTaskInfoBinding
import com.divesh.perfex.tasks.presentation.view.tabs.ViewTaskViewModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class TaskInfoFragment : CoreFragment() {
    private var _binding: FragmentTaskInfoBinding? = null
    private val binding get() = _binding!!
    private val viewModel: ViewTaskViewModel by viewModels()
    private var staffList = arrayOf<String>()

    private var checkedAssignees: BooleanArray? = null
    private var selectedAssigneesPositions: ArrayList<Int> = ArrayList()

    private var checkedfollowers: BooleanArray? = null
    private var selectedFollowersPositions: ArrayList<Int> = ArrayList()
    override fun onPermissionsChanged(permissions: Map<String, Boolean>) {

    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentTaskInfoBinding.inflate(inflater, container, false)
        observeViewModel()
        setData()
        return binding.root
    }

    private fun observeViewModel() {
        viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
        viewModel.internetProblem.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root,  getString(R.string.no_internet))
            }
        }
        viewModel.responseMessage.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }
        viewModel.loading.observe(viewLifecycleOwner) {
            if (it) {
                binding.progressBar.visibility = View.VISIBLE
            } else {
                binding.progressBar.visibility = View.GONE
            }
        }
        viewModel.viewTaskResponse.observe(viewLifecycleOwner) {
            if (it != null) {
                if (it.staff?.isNotEmpty() == true) {
                    checkedfollowers = BooleanArray(it.staff.size)
                    checkedAssignees = BooleanArray(it.staff.size)
                    it.staff.forEachIndexed { index, staff ->
                        staffList += "${staff.firstname} ${staff.lastname}"
                        it.task.assignees_ids?.forEach {assigneeId->
                            if(assigneeId == staff.staffid)
                                checkedAssignees?.set(index, true)
                        }
                        it.task.followers_ids?.forEach { followerId->
                            if(followerId == staff.staffid)
                                checkedAssignees?.set(index, true)
                        }
                        checkedfollowers?.set(index, true)
                    }
                    Extensions().transformIntoMultiSelectPicker(
                        binding.assignees,
                        requireContext(),
                        staffList,
                        checkedAssignees,
                        getString(R.string.select_assignees)
                    ) { selectedPositions ->
                        selectedAssigneesPositions = selectedPositions
                        val selectedAssigneeString = StringBuilder()
                        checkedAssignees = BooleanArray(it.staff.size)
                        var selectedAssigneeIds = ""
                        selectedPositions.forEachIndexed { index, position ->
                            selectedAssigneeIds += "${it.staff[position].staffid},"
                            selectedAssigneeString.append(
                                if ((index + 1) == selectedPositions.size) {
                                    "${it.staff[position].firstname} ${it.staff[position].lastname}"
                                } else {
                                    "${it.staff[position].firstname} ${it.staff[position].lastname},"
                                }
                            )
                            checkedAssignees?.set(index, true)
                        }
                        binding.assignees.text = selectedAssigneeString.toString()
                        viewModel.updateTaskAssignees(selectedAssigneeIds, arguments?.getString("id").toString())
                    }

                    Extensions().transformIntoMultiSelectPicker(
                        binding.followers,
                        requireContext(),
                        staffList,
                        checkedfollowers,
                        getString(R.string.select_followers)
                    ) { selectedPositions ->
                        selectedFollowersPositions = selectedPositions
                        val selectedFollowersString = StringBuilder()
                        var selectedFollowerIds = ""
                        checkedfollowers = BooleanArray(it.staff.size)
                        selectedPositions.forEachIndexed { index, position ->
                            selectedFollowerIds += "${it.staff[position].staffid},"
                            selectedFollowersString.append(
                                if ((index + 1) == selectedPositions.size) {
                                    "${it.staff[position].firstname} ${it.staff[position].lastname}"
                                } else {
                                    "${it.staff[position].firstname} ${it.staff[position].lastname},"
                                }
                            )
                            checkedfollowers?.set(index, true)
                        }
                        binding.followers.text = selectedFollowersString.toString()
                        viewModel.updateTaskFollowers(selectedFollowerIds, arguments?.getString("id").toString())
                    }
                }

                if(it.task.assignees?.isNotEmpty() == true){
                    var assigneeNames = ""
                    it.task.assignees.forEach { assignee ->
                        assigneeNames += "${assignee.firstname} ${assignee.lastname} ,"
                    }
                    binding.assignees.text = assigneeNames
                }
                if(it.task.followers?.isNotEmpty() == true){
                    var followerNames = ""
                    it.task.followers.forEach { follower ->
                        followerNames += "${follower.full_name} ,"
                    }
                    binding.followers.text = followerNames
                }
            }
        }
    }

    private fun setData() {
        when (arguments?.getString("STATUS")) {
            "1" -> {
                Extensions().setText(
                    binding.status,
                    binding.root.context.getString(R.string.not_started)
                )
            }
            "2" -> {
                Extensions().setText(
                    binding.status,
                    binding.root.context.getString(R.string.waiting_feedback)
                )
            }
            "3" -> {
                Extensions().setText(
                    binding.status,
                    binding.root.context.getString(R.string.testing)
                )
            }
            "4" -> {
                Extensions().setText(
                    binding.status,
                    binding.root.context.getString(R.string.in_progress)
                )
            }
            "5" -> {
                Extensions().setText(
                    binding.status,
                    binding.root.context.getString(R.string.not_started)
                )
            }
        }
        when (arguments?.getString("priority")) {
            "1" -> {
                Extensions().setText(
                    binding.priority,
                    binding.root.context.getString(R.string.low_priority)
                )
            }
            "2" -> {
                Extensions().setText(
                    binding.priority,
                    binding.root.context.getString(R.string.medium_priority)
                )
            }
            "3" -> {
                Extensions().setText(
                    binding.priority,
                    binding.root.context.getString(R.string.high_priority)
                )
            }
            "4" -> {
                Extensions().setText(
                    binding.priority,
                    binding.root.context.getString(R.string.urgent_priority)
                )
            }
        }
        if (arguments?.getString("billable") == "1")
            Extensions().setText(binding.billable, getString(R.string.yes))
        else
            Extensions().setText(binding.billable, getString(R.string.no))
        if (arguments?.getString("billed") == "1")
            Extensions().setText(binding.billable, getString(R.string.yes))
        else
            Extensions().setText(binding.billable, getString(R.string.no))
        Extensions().setText(binding.dueDate, arguments?.getString("duedate"))
        Extensions().setText(binding.hourlyRate, arguments?.getString("hourly_rate"))
        Extensions().setText(binding.startDate, arguments?.getString("startdate"))
        Extensions().setText(binding.name, arguments?.getString("task_name"))
        if (arguments?.getString("description") != null && arguments?.getString("description") != "null") {
            binding.description.text = arguments?.getString("description")!!.parseAsHtml()
        }
        Extensions().setText(
            binding.totalLoggedTime,
            arguments?.getString("total_logged_time")?.substringBefore(".")
        )
        Extensions().setText(
            binding.myLoggedTime,
            arguments?.getString("my_logged_time")?.substringBefore(".")
        )
    }
}