package com.divesh.perfex.leads.domain.interfaces

import com.divesh.perfex.leads.domain.models.LeadNotes


interface LeadNotesInterface {
    fun deleteNote(id: Int, position: Int)
    fun editNote(note: LeadNotes.Note)
}