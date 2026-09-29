package com.divesh.perfex.core.helpers.dynamic_form

import android.content.Context
import android.content.res.ColorStateList
import android.graphics.drawable.Drawable
import android.text.InputType
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.AutoCompleteTextView
import android.widget.FrameLayout
import android.widget.LinearLayout
import androidx.appcompat.view.ContextThemeWrapper
import androidx.appcompat.widget.AppCompatTextView
import androidx.appcompat.widget.LinearLayoutCompat
import androidx.core.content.ContextCompat
import androidx.core.view.forEach
import androidx.core.view.isNotEmpty
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.core.helpers.dynamic_form.models.DynamicFormValues
import com.divesh.perfex.leads.domain.models.CustomField
import com.divesh.perfex.leads.domain.models.ViewLeadResponseModel
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout


class DynamicFormHelper(
    private val context: Context,
    private val customFields: List<CustomField?>,
    private val customFieldsRootView: LinearLayoutCompat,
    private val customFieldsTo: String,
) {
    private val requiredFieldIds = arrayListOf<Int>()
    private var customFieldValues: List<Any?>? = null

    fun setCustomFieldValues(customFieldValues: List<Any?>?) {
        this.customFieldValues = customFieldValues
    }

    fun buildAndApplyForm() {
        if (customFields.isNotEmpty()) {
            val color = R.color.themeColorPrimary
            val defaultColor = ContextCompat.getColor(context, R.color.themeColorPrimary)
            val states = arrayOf(
                intArrayOf(android.R.attr.state_focused),
                intArrayOf(android.R.attr.state_hovered),
                intArrayOf(android.R.attr.state_enabled),
                intArrayOf()
            )

            val colors = intArrayOf(
                color, // focused color
                color, // hovered color
                color, // enabled color
                defaultColor
            ) // default color

            val myColorList = ColorStateList(states, colors)
            val layoutParameters = ViewGroup.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
            val linearLayoutParams = LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            )
            val drawableEditText =
                ContextCompat.getDrawable(context, R.drawable.ic_baseline_sticky_note_2_24)?.apply {
                    setTint(ContextCompat.getColor(context, R.color.themeColorPrimary))
                }
            val extension = Extensions()
            customFieldsRootView.removeAllViews()
            customFields.forEach { customField ->
                if (customField?.fieldto == customFieldsTo) {
                    if (customField.required == 1) {
                        requiredFieldIds += customField.id
                    }
                    when (customField.type) {
                        FIELD_TYPE_INPUT, FIELD_TYPE_NUMBER, FIELD_TYPE_TEXTAREA -> {
                            addTextInputBoxToView(customField, myColorList)
                        }
                        FIELD_TYPE_SELECT -> {
                            addSelectInputBoxToView(customField, linearLayoutParams)
                        }
                        FIELD_TYPE_MULTI_SELECT -> {
                            addMultiSelectInputBoxToView(
                                customField,
                                layoutParameters,
                                drawableEditText,
                                extension
                            )
                        }
                        FIELD_TYPE_DATE_PICKER, FIELD_TYPE_DATE_PICKER_TIME -> {
                            addDateAndTimePickerInputBoxToView(
                                customField,
                                layoutParameters,
                                drawableEditText,
                                extension
                            )
                        }
                    }
                    addBlankViewForSpacingInBetween()
                }
            }
        }
    }

    private fun addTextInputBoxToView(customField: CustomField, myColorList: ColorStateList) {
        val textInputLayout = getTextInputLayout(
            customField.name.toString(),
            customField.id,
            customField.required,
            myColorList
        )
        val textInputEditText = TextInputEditText(textInputLayout.context).apply {
            inputType = when (customField.type) {
                FIELD_TYPE_NUMBER -> {
                    InputType.TYPE_NUMBER_FLAG_DECIMAL
                }
                FIELD_TYPE_TEXTAREA -> {
                    isSingleLine = false
                    InputType.TYPE_CLASS_TEXT
                }
                else -> {
                    InputType.TYPE_TEXT_FLAG_AUTO_COMPLETE
                }
            }
            setPadding(30, 30, 30, 30)
            val value = getCustomFieldValueById(customField)
            if (value != null)
                setText(value)
        }
        textInputLayout.addView(textInputEditText)
        customFieldsRootView.addView(textInputLayout)
    }

    private fun getCustomFieldValueById(customField: CustomField): String? {
        var value: String? = ""
        if (customFieldValues != null) {
            if (customFieldsTo == "leads") {
                val customFieldValues =
                    customFieldValues as List<ViewLeadResponseModel.Lead.CustomFieldsValue>
                customFieldValues.forEach {
                    if (it.fieldid == customField.id) {
                        value = it.value
                        return@forEach
                    }
                }
            }
        }
        return value
    }

    private fun addSelectInputBoxToView(
        customField: CustomField,
        linearLayoutParams: LinearLayout.LayoutParams
    ) {
        val autoCompleteTextView = AutoCompleteTextView(context)
        val textInputLayout = TextInputLayout(
            ContextThemeWrapper(
                context,
                R.style.Widget_MaterialComponents_TextInputLayout_OutlinedBox_Dense_ExposedDropdownMenu
            )
        )
        autoCompleteTextView.apply {
            id = customField.id
            inputType = InputType.TYPE_NULL
        }
        textInputLayout.apply {
            id = View.generateViewId()
            hint = customField.name
            boxBackgroundColor = ContextCompat.getColor(
                context,
                android.R.color.transparent
            )
            boxBackgroundMode = TextInputLayout.BOX_BACKGROUND_OUTLINE
            setStartIconDrawable(R.drawable.ic_baseline_sticky_note_2_24)
            setStartIconTintList(
                ContextCompat.getColorStateList(
                    context,
                    R.color.themeColorPrimary
                )
            )
            background = ContextCompat.getDrawable(context, R.drawable.border)
            setPadding(0, 30, 0, 30)
            setBoxCornerRadii(5f, 5f, 5f, 5f)
        }
        if (customField.options != null) {
            val items = arrayListOf<String>()
            val optionsArray = customField.options.split(",")
            if (optionsArray.isNotEmpty()) {
                for (item in optionsArray) {
                    items.add(item)
                }
                val adapter = ArrayAdapter(context, R.layout.list_item, items)
                autoCompleteTextView.setAdapter(adapter)
                if (customField.default_value != null && customField.default_value.isNotBlank()) {
                    autoCompleteTextView.setText(customField.default_value, false)
                } else {
                    val value = getCustomFieldValueById(customField)
                    if (value != null)
                        autoCompleteTextView.setText(value, false)
                }
            }
        }
        textInputLayout.addView(autoCompleteTextView, linearLayoutParams)
        customFieldsRootView.addView(textInputLayout, linearLayoutParams)
    }

    private fun addMultiSelectInputBoxToView(
        customField: CustomField,
        layoutParameters: ViewGroup.LayoutParams,
        drawableEditText: Drawable?,
        extension: Extensions
    ) {
        val appCompatTextView = AppCompatTextView(context).apply {
            layoutParams = layoutParameters
            background = ContextCompat.getDrawable(context, R.drawable.border)
            setPadding(15, 30, 15, 30)
            setCompoundDrawablesWithIntrinsicBounds(
                drawableEditText,
                null,
                null,
                null
            )
            compoundDrawablePadding = 5
            id = customField.id
            hint = customField.name
        }

        customFieldsRootView.addView(appCompatTextView)
        var items: Array<String> = arrayOf()
        var selectedGroups: BooleanArray?
        if (customField.options != null) {
            val optionsArray = customField.options.split(",")
            if (optionsArray.isNotEmpty()) {
                val selectedOptions = getCustomFieldValueById(customField)?.split(",")?.map { it.trim() }
                selectedGroups = BooleanArray(optionsArray.size)
                var selectedOptionNames = ""
                optionsArray.forEachIndexed { index, item ->
                    items += item
                    if(selectedOptions == null) {
                        if (customField.default_value == item) {
                            selectedGroups?.set(index, true)
                            selectedOptionNames += "$item,"
                        }
                    }else{
                        if(selectedOptions.contains(item)){
                            selectedGroups?.set(index, true)
                            selectedOptionNames += "$item,"
                        }
                    }
                }
                if(selectedOptionNames != "" && selectedOptionNames.last() == ',')
                    selectedOptionNames = selectedOptionNames.dropLast(1)
                appCompatTextView.text = selectedOptionNames
            } else {
                selectedGroups = BooleanArray(items.size)
            }
            extension.transformIntoMultiSelectPicker(
                appCompatTextView,
                context,
                items,
                selectedGroups,
                "Select ${customField.name}"
            ) { selectedPositions ->
                val selectedGroupString = StringBuilder()
                selectedGroups = BooleanArray(items.size)
                selectedPositions.forEachIndexed { index, id ->
                    selectedGroupString.append(
                        if ((index + 1) == selectedPositions.size) {
                            items[id]
                        } else {
                            "${items[id]},"
                        }
                    )
                    selectedGroups?.set(index, true)
                }
                appCompatTextView.text = selectedGroupString.toString()
            }
        }
    }

    private fun addDateAndTimePickerInputBoxToView(
        customField: CustomField,
        layoutParameters: ViewGroup.LayoutParams,
        drawableEditText: Drawable?,
        extension: Extensions
    ) {
        val appCompatTextView = AppCompatTextView(context).apply {
            layoutParams = layoutParameters
            background = ContextCompat.getDrawable(context, R.drawable.border)
            setPadding(15, 30, 15, 30)
            setCompoundDrawablesWithIntrinsicBounds(
                drawableEditText,
                null,
                null,
                null
            )
            compoundDrawablePadding = 5
            id = customField.id
            hint = customField.name
            val value = getCustomFieldValueById(customField)
            if (value != null)
                text = value
        }

        customFieldsRootView.addView(appCompatTextView)
        if (customField.type == FIELD_TYPE_DATE_PICKER)
            extension.transformIntoDatePicker(
                appCompatTextView,
                context,
                null,
                null,
                "yyyy-MM-dd"
            )
        else
            extension.transformIntoDateTimepicker(appCompatTextView, context)
    }

    private fun addBlankViewForSpacingInBetween() {
        val view = View(context).apply {
            layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, 20)
        }
        customFieldsRootView.addView(view)
    }

    private fun getTextInputLayout(
        fieldName: String,
        fieldId: Int,
        required: Int,
        myColorList: ColorStateList,
        isSelect: Boolean = false
    ): TextInputLayout {
        val textInputLayout = if (isSelect) {
            TextInputLayout(context)
        } else {
            TextInputLayout(context)
        }
        return textInputLayout.apply {
            hint = fieldName
            if (!isSelect) {
                endIconMode = TextInputLayout.END_ICON_CLEAR_TEXT
            }
            boxBackgroundMode = TextInputLayout.BOX_BACKGROUND_OUTLINE
            isCounterEnabled = true
            startIconDrawable = ContextCompat.getDrawable(
                context,
                R.drawable.ic_baseline_sticky_note_2_24
            )
            setStartIconTintList(
                ContextCompat.getColorStateList(
                    context,
                    R.color.themeColorPrimary
                )
            )
            boxStrokeColor =
                ContextCompat.getColor(context, R.color.themeColorPrimary)
            boxStrokeWidth = 1
            boxBackgroundColor = ContextCompat.getColor(context, R.color.white)
            id = fieldId
            if (required == 1) {
                isErrorEnabled = true
            }
            setBoxStrokeColorStateList(myColorList)
            setBoxCornerRadii(5f, 5f, 5f, 5f)
        }
    }

    fun validateForm(): Boolean {
        var isValid = true
        if (customFieldsRootView.isNotEmpty() && requiredFieldIds.isNotEmpty()) {
            customFieldsRootView.forEach { customView ->
                if (requiredFieldIds.contains(customView.id)) {
                    if (customView is TextInputLayout) {
                        if (customView.editText?.text.toString().isBlank()) {
                            isValid = false
                            customView.error = context.getString(R.string.field_required)
                        }
                    } else if (customView is AppCompatTextView) {
                        if (customView.text.toString().isBlank()) {
                            isValid = false
                            customView.error = context.getString(R.string.field_required)
                        }
                    }
                }
            }
        }
        return isValid
    }

    fun getFormData(): DynamicFormValues {
        var customFieldValues: List<DynamicFormValues.CustomFieldValue> = arrayListOf()
        if (customFieldsRootView.isNotEmpty()) {
            customFieldsRootView.forEach { customView ->
                if (customView is TextInputLayout) {
                    val innerView = customView.getChildAt(0)
                    var viewToCheck = innerView
                    if (innerView is FrameLayout) {
                        viewToCheck = innerView.getChildAt(0)
                    }
                    if (viewToCheck is AutoCompleteTextView) {
                        customFieldValues = customFieldValues + DynamicFormValues.CustomFieldValue(
                            viewToCheck.id,
                            viewToCheck.text.toString()
                        )
                    } else if (viewToCheck is TextInputEditText) {
                        customFieldValues = customFieldValues + DynamicFormValues.CustomFieldValue(
                            customView.id,
                            viewToCheck.text.toString()
                        )
                    }
                } else if (customView is AppCompatTextView) {
                    customFieldValues = customFieldValues + DynamicFormValues.CustomFieldValue(
                        customView.id,
                        customView.text.toString()
                    )
                }
            }
        }
        return DynamicFormValues(customFieldValues)
    }

    companion object {
        const val FIELD_TYPE_INPUT = "input"
        const val FIELD_TYPE_NUMBER = "number"
        const val FIELD_TYPE_TEXTAREA = "textarea"
        const val FIELD_TYPE_SELECT = "select"
        const val FIELD_TYPE_MULTI_SELECT = "multiselect"
        const val FIELD_TYPE_DATE_PICKER = "date_picker"
        const val FIELD_TYPE_DATE_PICKER_TIME = "date_picker_time"
    }
}