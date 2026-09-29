package com.divesh.perfex.tasks.domain.interfaces

import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel

interface AttachmentInterface {
    fun onAttachmentClicked(attachment: ViewTaskResponseModel.Task.Attachment)
    fun onAttachmentRemoved(attachment: ViewTaskResponseModel.Task.Attachment)
}