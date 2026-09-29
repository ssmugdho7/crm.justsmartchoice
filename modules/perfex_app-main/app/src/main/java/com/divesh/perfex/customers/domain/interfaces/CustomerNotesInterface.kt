package com.divesh.perfex.customers.domain.interfaces

import com.divesh.perfex.customers.domain.models.CustomerNotesResponseModel


interface CustomerNotesInterface {
    fun onNoteDeleted(id: Int, position: Int)
    fun onEditBtnClicked(record: CustomerNotesResponseModel.Note )
}