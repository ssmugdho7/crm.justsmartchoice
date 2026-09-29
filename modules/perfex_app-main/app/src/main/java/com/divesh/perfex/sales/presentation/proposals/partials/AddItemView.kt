package com.divesh.perfex.sales.presentation.proposals.partials

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.Space
import androidx.core.widget.doOnTextChanged
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.AddItemViewBinding
import com.divesh.perfex.sales.domain.interfaces.AddItemInterface
import com.divesh.perfex.sales.domain.models.item.Item
import com.divesh.perfex.sales.domain.models.item.Tax
import com.divesh.perfex.sales.domain.models.proposals.SelectedItem

class AddItemView(
    private val context: Context,
    private val taxList: List<Tax?>?,
    private val item: Item?,
    private val index: Int,
    private val listener: AddItemInterface
) {
    private var taxes = arrayOf<String>()
    private var currentItem: SelectedItem? = null
    private var checkedTaxes: BooleanArray? = null
    private var selectedTaxesPositions: ArrayList<Int> = ArrayList()
    private lateinit var binding: AddItemViewBinding
    private lateinit var view: View
    init {
        initialize()
    }

    private fun initialize() {
        binding = AddItemViewBinding.inflate(LayoutInflater.from(context))
        view = binding.root
        setCurrentItem()
        setDataToView()
        setClickListeners()
    }

    private fun setCurrentItem() {
        if(item != null) {
            currentItem = SelectedItem(
                item.id,
                item.description,
                item.long_description,
                item.unit,
                item.rate,
                1,
                arrayListOf()
            )
        }
    }

    private fun setDataToView() {
        if (item != null) {
            binding.qty.editText?.setText("1")
            binding.description.editText?.setText(item.description)
            binding.longDescription.editText?.setText(item.long_description)
            binding.rate.editText?.setText(item.rate.toString())
            binding.totalAmount.text = context.getString(
                R.string.data_with_title,
                "Amount", item.rate.toString()
            )
        }
        if (taxList?.isNotEmpty() == true) {
            taxList.forEach {
                if(it?.name?.isNotEmpty() == true){
                    taxes += it.name
                }
            }
            checkedTaxes = BooleanArray(taxList.size)
            Extensions().transformIntoMultiSelectPicker(binding.tax, context, taxes,  checkedTaxes,"Select Taxes") { selectedPositions ->
                selectedTaxesPositions = selectedPositions
                val selectedTaxesString = StringBuilder()
                checkedTaxes = BooleanArray(taxList.size)
                var selectedTaxes: List<Tax?> = arrayListOf()
                selectedPositions.forEachIndexed { index, taxPosition ->
                    selectedTaxesString.append(if((index+1) == selectedPositions.size){
                        taxes[taxPosition]
                    }else{
                        "${taxes[taxPosition]},"
                    })
                    checkedTaxes?.set(index, true)
                }
                binding.tax.text = selectedTaxesString.toString()
                taxList.forEachIndexed{ index: Int, tax: Tax? ->
                    if(selectedPositions.contains(index)){
                        selectedTaxes = selectedTaxes + tax
                    }
                }
                currentItem?.taxes = selectedTaxes
                listener.onItemUpdated(currentItem!!, index)
            }
        }
    }

    private fun setClickListeners() {
        binding.qty.editText?.doOnTextChanged { text, _, _, _ ->
            if (text.toString().isNotBlank()) {
                val qty = text.toString().toInt()
                binding.totalAmount.text = context.getString(
                    R.string.data_with_title,
                    "Amount",
                    (qty * item!!.rate).toString()
                )
                currentItem?.qty = qty
                currentItem?.rate = item.rate
                listener.onItemUpdated(currentItem!!, index)
            }
        }

        binding.setBtn.setOnClickListener {
            binding.setBtn.visibility = View.GONE
            if(currentItem != null) {
                listener.onItemAdded(currentItem!!, index)
            }
        }
        binding.removeBtn.setOnClickListener {
            binding.root.removeAllViews()
            if(currentItem != null) {
                listener.onItemRemoved(currentItem!!, index)
            }
        }
    }

    fun getView(): View {
        return this.view
    }

    fun getSpaceView(): Space {
        val spaceView = Space(context)
        val layoutParams = LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, 40)
        spaceView.layoutParams = layoutParams
        return spaceView
    }
}