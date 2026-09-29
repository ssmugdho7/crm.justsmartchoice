package com.divesh.perfex.support.domain.interfaces

import com.divesh.perfex.support.domain.models.SupportResponseModel

interface SupportInterface {
    fun onEditBtnClicked(record: SupportResponseModel.Ticket)
    fun onDeleteBtnClicked(id: Int, position: Int)
    fun onViewPublicFormClicked(record: SupportResponseModel.Ticket)
}