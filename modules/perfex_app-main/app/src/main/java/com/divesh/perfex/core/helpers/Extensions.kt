package com.divesh.perfex.core.helpers

import android.app.DatePickerDialog
import android.app.TimePickerDialog
import android.content.ContentResolver
import android.content.Context
import android.content.Intent
import android.graphics.Color
import android.net.Uri
import android.provider.OpenableColumns
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.widget.AppCompatTextView
import androidx.core.content.ContextCompat
import com.google.android.material.snackbar.Snackbar
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.rajat.pdfviewer.PdfViewerActivity
import java.io.ByteArrayOutputStream
import java.io.File
import java.io.IOException
import java.io.InputStream
import java.text.SimpleDateFormat
import java.util.*

class Extensions {
    fun simpleAlert(title: String, context: Context, callback: ((isConfirmed: Boolean) -> Unit)?) {
        val builder: AlertDialog.Builder = AlertDialog.Builder(context)
        builder.setMessage(title)
        builder.setCancelable(false)
        builder.setPositiveButton("Yes") { dialog, _ ->
            dialog.cancel()
            if (callback != null) {
                callback(true)
            }
        }

        builder.setNegativeButton("No") { dialog, _ ->
            dialog.cancel()
            if (callback != null) {
                callback(false)
            }
        }

        val alert: AlertDialog = builder.create()
        alert.setOnShowListener {
            alert.getButton(AlertDialog.BUTTON_NEGATIVE)
                .setTextColor(ContextCompat.getColor(context, R.color.KellyGreen))
            alert.getButton(AlertDialog.BUTTON_POSITIVE)
                .setTextColor(ContextCompat.getColor(context, R.color.FerrariRed))
        }
        alert.show()
    }

    fun transformIntoMultiSelectPicker(
        view: AppCompatTextView,
        context: Context,
        array: Array<String>,
        checkedPositions: BooleanArray?,
        title: String,
        positiveCallback: ((ArrayList<Int>) -> Unit)?
    ) {
        view.setOnClickListener {
            val selectedList: ArrayList<Int> = ArrayList()
            checkedPositions?.forEachIndexed { i, b ->
                if (b) {
                    selectedList.add(i)
                    selectedList.sort()
                }
            }
            val builder: AlertDialog.Builder = AlertDialog.Builder(context)
            // set title
            builder.setTitle(title)
            // set dialog non cancelable
            builder.setCancelable(false)
            builder.setMultiChoiceItems(array, checkedPositions) { _, i, isChecked ->
                // check condition
                if (isChecked) {
                    selectedList.add(i)
                    selectedList.sort()
                } else {
                    selectedList.remove(Integer.valueOf(i))
                }
            }
            builder.setNeutralButton(
                "Clear All"
            ) { _, _ ->
                selectedList.clear()
                view.text = ""
            }
            builder.setPositiveButton(
                "OK"
            ) { dialogInterface, _ ->
                dialogInterface.cancel()
                if (positiveCallback != null) {
                    positiveCallback(selectedList)
                }
            }
            builder.setNegativeButton(
                "Cancel"
            ) { dialogInterface, _ -> // dismiss dialog
                dialogInterface.dismiss()
            }
            builder.show()
        }
    }

    fun transformIntoDatePicker(
        textView: AppCompatTextView,
        context: Context,
        minDate: Date? = null,
        maxDate: Date? = null,
        format: String = "dd-MMM-yy",
        setDefaultDateAsToday: Boolean = false
    ) {
        textView.isFocusableInTouchMode = false
        textView.isClickable = true
        textView.isFocusable = false

        val myCalendar = Calendar.getInstance()
        if(setDefaultDateAsToday){
            val sdf = SimpleDateFormat(format, Locale.getDefault())
            textView.text = sdf.format(Date().time)
        }
        val datePickerOnDataSetListener =
            DatePickerDialog.OnDateSetListener { _, year, monthOfYear, dayOfMonth ->
                myCalendar.set(Calendar.YEAR, year)
                myCalendar.set(Calendar.MONTH, monthOfYear)
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth)
                val sdf = SimpleDateFormat(format, Locale.getDefault())
                textView.text = sdf.format(myCalendar.time)
            }

        textView.setOnClickListener {
            DatePickerDialog(
                context,
                datePickerOnDataSetListener,
                myCalendar.get(Calendar.YEAR),
                myCalendar.get(Calendar.MONTH),
                myCalendar.get(Calendar.DAY_OF_MONTH)
            ).run {
                maxDate?.time?.also { datePicker.maxDate = it }
                minDate?.time?.also { datePicker.minDate = it }
                show()
            }
        }
    }

    fun transformIntoDateTimepicker(
        view: AppCompatTextView, context: Context, minDate: Date = Date(), maxDate: Date? = null
    ) {
        val format = "yyyy-MM-dd"
        view.isFocusableInTouchMode = false
        view.isClickable = true
        view.isFocusable = false

        val myCalendar = Calendar.getInstance()
        val datePickerOnDataSetListener =
            DatePickerDialog.OnDateSetListener { _, year, monthOfYear, dayOfMonth ->
                myCalendar.set(Calendar.YEAR, year)
                myCalendar.set(Calendar.MONTH, monthOfYear)
                myCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth)
                val sdf = SimpleDateFormat(format, Locale.UK)

                val formattedDate = sdf.format(myCalendar.time)
                val mCurrentTime = Calendar.getInstance()
                val hour = mCurrentTime[Calendar.HOUR_OF_DAY]
                val minute = mCurrentTime[Calendar.MINUTE]
                val mTimePicker = TimePickerDialog(
                    context,
                    R.style.DatePickerTheme,
                    { _, selectedHour, selectedMinute ->
                        var hour1 = selectedHour.toString()
                        var minute1 = selectedMinute.toString()
                        myCalendar.set(Calendar.HOUR, selectedHour)
                        myCalendar.set(Calendar.MINUTE, selectedMinute)
                        if (selectedHour < 10) {
                            hour1 = "0$selectedHour"
                        }
                        if (selectedMinute < 10) {
                            minute1 = "0$selectedMinute"
                        }
                        view.text = context.getString(
                            R.string.call_duration_with_date, formattedDate, hour1, minute1, "00"
                        )
                    },
                    hour,
                    minute,
                    false
                )
                mTimePicker.show()
            }

        view.setOnClickListener {
            DatePickerDialog(
                context,
                R.style.DatePickerTheme,
                datePickerOnDataSetListener,
                myCalendar.get(Calendar.YEAR),
                myCalendar.get(Calendar.MONTH),
                myCalendar.get(Calendar.DAY_OF_MONTH)
            ).run {
                datePicker.minDate = minDate.time
                if (maxDate != null) {
                    datePicker.maxDate = maxDate.time
                }
                show()
            }
        }
    }

    fun setText(textView: AppCompatTextView, text: String?) {
        if (!isTextNull(text)) {
            textView.text = text
        }
    }

    private fun isTextNull(text: String?): Boolean {
        var isTextNull = true
        if (text != null) {
            if (text.isNotEmpty()) {
                if (text != "null") {
                    isTextNull = false
                }
            }
        }
        return isTextNull
    }

    fun getAppLocale(context: Context): String? {
        val sharedPreferences = context.getSharedPreferences(
            "${Constants.packagePreFix}_preferences",
            Context.MODE_PRIVATE
        )
        return sharedPreferences.getString(Constants.defaultLanguage, null)
    }

    fun sendMail(
        context: Context,
        email: String,
        subject: String? = null,
        message: String? = null
    ) {
        val emailIntent = Intent(Intent.ACTION_SEND).apply {
            putExtra(Intent.EXTRA_EMAIL, arrayOf(email))
            if (subject != null) {
                putExtra(Intent.EXTRA_SUBJECT, subject)
            }
            if (message != null) {
                putExtra(Intent.EXTRA_TEXT, message)
            }
            type = "message/rfc822"
            setPackage("com.google.android.gm")
        }
        try {
            context.startActivity(Intent.createChooser(emailIntent, "Choose Email Client..."))
        } catch (e: Exception) {
            Toast.makeText(context, e.message, Toast.LENGTH_LONG).show()
        }
    }

    fun getFileNameFromPickedImageData(uri: Uri, context: Context): String? = when (uri.scheme) {
        ContentResolver.SCHEME_CONTENT -> getContentFileName(uri, context)
        else -> uri.path?.let(::File)?.name
    }

    private fun getContentFileName(uri: Uri, context: Context): String? = runCatching {
        context.contentResolver.query(uri, null, null, null, null)?.use { cursor ->
            cursor.moveToFirst()
            return@use cursor.getColumnIndexOrThrow(OpenableColumns.DISPLAY_NAME)
                .let(cursor::getString)
        }
    }.getOrNull()

    @Throws(IOException::class)
    fun getBytesFromInputStream(inputStream: InputStream?): ByteArray? {
        val byteBuff = ByteArrayOutputStream()
        val buffSize = 1024
        val buff = ByteArray(buffSize)
        var len = 0
        while (inputStream?.read(buff).also {
                if (it != null) {
                    len = it
                }
            } != -1) {
            byteBuff.write(buff, 0, len)
        }
        return byteBuff.toByteArray()
    }

    fun openUrl(url: String, context: Context) {
        val uri: Uri = Uri.parse(url)
        val intent = Intent(Intent.ACTION_VIEW, uri)
        context.startActivity(intent)
    }

    fun viewPdf(context: Context, url: String, title: String, enableDownload: Boolean) {
        context.startActivity(
            PdfViewerActivity.launchPdfFromUrl(
                context,
                url,
                title,
                "",
                enableDownload
            )
        )
    }

    fun showMessage(rootView: View, title: String, actionText: String? = null, listener: View.OnClickListener? = null){
        val snackBar = Snackbar.make(rootView, title, Snackbar.LENGTH_LONG)
        if(actionText != null && listener != null){
            snackBar.setAction(actionText, listener)
        }
        snackBar.setActionTextColor(Color.BLACK)
        snackBar.show()
    }
}