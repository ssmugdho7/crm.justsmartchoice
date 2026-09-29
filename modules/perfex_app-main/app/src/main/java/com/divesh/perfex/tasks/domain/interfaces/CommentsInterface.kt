package com.divesh.perfex.tasks.domain.interfaces

import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel

interface CommentsInterface {
    fun editComment(comment: ViewTaskResponseModel.Task.Comment)
    fun removeComment(comment: ViewTaskResponseModel.Task.Comment)
}