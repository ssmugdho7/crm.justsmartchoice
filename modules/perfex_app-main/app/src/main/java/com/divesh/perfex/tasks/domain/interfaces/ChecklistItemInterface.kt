package com.divesh.perfex.tasks.domain.interfaces

import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel

interface ChecklistItemInterface {
    fun onEdit(checklistItem: ViewTaskResponseModel.Task.ChecklistItems)
    fun onAssignStaff(checklistItem: ViewTaskResponseModel.Task.ChecklistItems)
    fun onSaveAsTemplate(checklistItem: ViewTaskResponseModel.Task.ChecklistItems)
    fun onCheckboxChecked(checklistItem: ViewTaskResponseModel.Task.ChecklistItems, isChecked: Boolean)
    fun onDelete(checklistItem: ViewTaskResponseModel.Task.ChecklistItems)
}