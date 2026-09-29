package com.divesh.perfex.sales.domain.interfaces

import com.divesh.perfex.sales.domain.models.proposals.SelectedItem

interface AddItemInterface {
    fun onItemAdded(item: SelectedItem, index: Int)
    fun onItemUpdated(item: SelectedItem, index: Int)
    fun onItemRemoved(item: SelectedItem, index: Int)
}