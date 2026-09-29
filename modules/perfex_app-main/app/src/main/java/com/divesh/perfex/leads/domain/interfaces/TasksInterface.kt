package com.divesh.perfex.leads.domain.interfaces

import com.divesh.perfex.leads.domain.models.Task

interface TasksInterface {
    fun onViewTaskClicked(task: Task)
    fun onEditTaskClicked(task: Task)
    fun startOrStopTimer(taskId: Int, notFinishedTimerByCurrentStaff: Int)
    fun deleteTask(id: Int, position: Int)
    fun updateTaskStatus(taskId: Int, selectedPosition: Int)
}