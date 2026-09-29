package com.divesh.perfex.core.helpers

import android.os.Environment
import org.apache.poi.hssf.usermodel.HSSFCellStyle
import org.apache.poi.hssf.usermodel.HSSFWorkbook
import org.apache.poi.hssf.util.HSSFColor
import org.apache.poi.ss.usermodel.Cell
import org.apache.poi.ss.usermodel.CellStyle
import org.apache.poi.ss.usermodel.Sheet
import org.apache.poi.ss.usermodel.Workbook
import java.io.IOException
import java.io.OutputStream

class ExportSampleToExcel {
    private var cell: Cell? = null
    private var sheet: Sheet? = null
    private var workbook: Workbook? = null
    private var headerCellStyle: CellStyle? = null

    fun exportDataIntoWorkbook(
        fileOutputStream: OutputStream,
        workbookName: String,
        type: String
    ): HashMap<Boolean, String> {
        
        // Check if available and not read only
        if (!isExternalStorageAvailable || isExternalStorageReadOnly) {
            val hashMapResponse =  HashMap<Boolean, String> ()
            hashMapResponse[false] = "Storage not available or read only"
            return hashMapResponse
        }

        // Creating a New HSSF Workbook (.xls format)
        workbook = HSSFWorkbook()
        setHeaderCellStyle()

        // Creating a New Sheet and Setting width for each column
        sheet = workbook?.createSheet(workbookName)
        sheet?.setColumnWidth(0, 15 * 400)
        sheet?.setColumnWidth(1, 15 * 400)
        when (type) {
            "Customer" -> {
                setHeaderRowForCustomer()
            }
            "CustomerTransactions" -> {
                setHeaderRowForCustomerTransactions()
            }
            "Supplier" -> {
                setHeaderRowForSupplier()
            }
            "SupplierTransactions" -> {
                setHeaderRowForSupplierTransactions()
            }
            else -> {
                setHeaderRowForCustomer()
            }
        }
        return storeExcelInStorage(fileOutputStream)
    }

    /**
     * Checks if Storage is READ-ONLY
     *
     * @return boolean
     */
    private val isExternalStorageReadOnly: Boolean
        get() {
            val externalStorageState = Environment.getExternalStorageState()
            return Environment.MEDIA_MOUNTED_READ_ONLY == externalStorageState
        }

    /**
     * Checks if Storage is Available
     *
     * @return boolean
     */
    private val isExternalStorageAvailable: Boolean
        get() {
            val externalStorageState = Environment.getExternalStorageState()
            return Environment.MEDIA_MOUNTED == externalStorageState
        }

    /**
     * Setup header cell style
     */
    private fun setHeaderCellStyle() {
        headerCellStyle = workbook!!.createCellStyle()
        headerCellStyle?.fillForegroundColor = HSSFColor.AQUA.index
        headerCellStyle?.fillPattern = HSSFCellStyle.SOLID_FOREGROUND
        headerCellStyle?.alignment = CellStyle.ALIGN_CENTER
    }

    /**
     * Setup Header Row and data for customers
     */
    private fun setHeaderRowForCustomer() {
        val headerRow = sheet!!.createRow(0)
        cell = headerRow.createCell(0)
        cell?.setCellValue("Customer Name")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(1)
        cell?.setCellValue("Number")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(2)
        cell?.setCellValue("Address")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(3)
        cell?.setCellValue("Send SMS")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(4)
        cell?.setCellValue("Date")
        cell?.cellStyle = headerCellStyle
        
        setSampleDataForCustomer()
    }

    private fun setSampleDataForCustomer() {
        // Create a New Row for every new entry in list
        val rowData = sheet!!.createRow(1)

        // Create Cells for each row
        cell = rowData.createCell(0)
        cell?.setCellValue("SAMPLE_NAME")

        cell = rowData.createCell(1)
        cell?.setCellValue("8955XXXXXX")

        cell = rowData.createCell(2)
        cell?.setCellValue("OPTIONAL_ADDRESS")

        cell = rowData.createCell(3)
        cell?.setCellValue("No")

        cell = rowData.createCell(4)
        cell?.setCellValue("2022-04-16")
    }

    private fun setHeaderRowForCustomerTransactions(){
        val headerRow = sheet!!.createRow(0)

        cell = headerRow.createCell(0)
        cell?.setCellValue("CustomerName")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(1)
        cell?.setCellValue("Amount")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(2)
        cell?.setCellValue("Type")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(3)
        cell?.setCellValue("Mode")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(4)
        cell?.setCellValue("Notes")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(5)
        cell?.setCellValue("Transaction ID")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(6)
        cell?.setCellValue("Date")
        cell?.cellStyle = headerCellStyle
        
        setSampleDataForCustomerTransactions()
    }

    private fun setSampleDataForCustomerTransactions(){
        // Create a New Row for every new entry in list
        val rowData = sheet!!.createRow(1)

        // Create Cells for each row
        cell = rowData.createCell(0)
        cell?.setCellValue("SAMPLE_CUSTOMER_NAME")

        // Create Cells for each row
        cell = rowData.createCell(1)
        cell?.setCellValue("0.0")

        cell = rowData.createCell(2)
        cell?.setCellValue("Sent/Received")

        cell = rowData.createCell(3)
        cell?.setCellValue("Cash/Bank/Wallet")

        cell = rowData.createCell(4)
        cell?.setCellValue("OPTIONAL_NOTES")

        cell = rowData.createCell(5)
        cell?.setCellValue("OPTIONAL_TRANSACTION_ID_")

        cell = rowData.createCell(6)
        cell?.setCellValue("2022-04-14")
    }
    
    /**
     * Setup Header Row and data for suppliers
     */
    private fun setHeaderRowForSupplier() {
        val headerRow = sheet!!.createRow(0)
        cell = headerRow.createCell(0)
        cell?.setCellValue("Name")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(1)
        cell?.setCellValue("Number")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(2)
        cell?.setCellValue("Address")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(3)
        cell?.setCellValue("Send SMS")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(4)
        cell?.setCellValue("DateTime")
        cell?.cellStyle = headerCellStyle

        setSampleDataForSupplier()
    }

    private fun setSampleDataForSupplier() {
        // Create a New Row for every new entry in list
        val rowData = sheet!!.createRow(1)

        // Create Cells for each row
        cell = rowData.createCell(0)
        cell?.setCellValue("SAMPLE_NAME")

        cell = rowData.createCell(1)
        cell?.setCellValue("8955XXXXXX")

        cell = rowData.createCell(2)
        cell?.setCellValue("OPTIONAL_ADDRESS")

        cell = rowData.createCell(3)
        cell?.setCellValue("No")

        cell = rowData.createCell(4)
        cell?.setCellValue("2022-04-16")
    }

    private fun setHeaderRowForSupplierTransactions(){
        val headerRow = sheet!!.createRow(0)

        cell = headerRow.createCell(0)
        cell?.setCellValue("SupplierName")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(1)
        cell?.setCellValue("Amount")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(2)
        cell?.setCellValue("Type")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(3)
        cell?.setCellValue("Mode")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(4)
        cell?.setCellValue("Notes")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(5)
        cell?.setCellValue("Transaction ID")
        cell?.cellStyle = headerCellStyle

        cell = headerRow.createCell(6)
        cell?.setCellValue("DateTime")
        cell?.cellStyle = headerCellStyle

        setSampleDataForSupplierTransactions()
    }

    private fun setSampleDataForSupplierTransactions(){
        // Create a New Row for every new entry in list
        val rowData = sheet!!.createRow(1)

        // Create Cells for each row
        cell = rowData.createCell(0)
        cell?.setCellValue("SAMPLE_SUPPLIER_NAME")

        // Create Cells for each row
        cell = rowData.createCell(1)
        cell?.setCellValue("0.0")

        cell = rowData.createCell(2)
        cell?.setCellValue("Purchased/Paid")

        cell = rowData.createCell(3)
        cell?.setCellValue("Cash/Bank/Wallet")

        cell = rowData.createCell(4)
        cell?.setCellValue("OPTIONAL_NOTES")

        cell = rowData.createCell(5)
        cell?.setCellValue("OPTIONAL_TRANSACTION_ID_")

        cell = rowData.createCell(6)
        cell?.setCellValue("2022-04-14")
    }
    
    
    //Store data in excel
    private fun storeExcelInStorage(fileOutputStream: OutputStream): HashMap<Boolean, String> {
        val hashMapResponse = HashMap<Boolean, String> ()
        try {
            workbook!!.write(fileOutputStream)
            hashMapResponse[true] = "File saved successfully!"
        } catch (e: IOException) {
            hashMapResponse[false] = e.message.toString()
        } catch (e: Exception) {
            hashMapResponse[false] = e.message.toString()
        } finally {
            try {
                fileOutputStream.close()
            } catch (ex: Exception) {
                ex.printStackTrace()
                hashMapResponse[false] = ex.message.toString()
            }
        }
        return hashMapResponse
    }
}