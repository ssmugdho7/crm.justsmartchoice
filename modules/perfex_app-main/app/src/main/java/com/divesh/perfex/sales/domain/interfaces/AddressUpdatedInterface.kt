package com.divesh.perfex.sales.domain.interfaces

import com.divesh.perfex.sales.domain.models.AddressPickerData

interface AddressUpdatedInterface {
    fun onAddressUpdated(addressPickerData: AddressPickerData?)
}