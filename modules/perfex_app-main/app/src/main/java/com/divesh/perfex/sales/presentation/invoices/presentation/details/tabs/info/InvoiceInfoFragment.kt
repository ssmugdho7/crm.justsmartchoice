package com.divesh.perfex.sales.presentation.invoices.presentation.details.tabs.info

import android.os.Build
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ProgressBar
import androidx.appcompat.widget.PopupMenu
import androidx.core.content.ContextCompat
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.divesh.perfex.R
import com.divesh.perfex.core.constants.Constants
import com.divesh.perfex.core.constants.invoices.InvoiceStatus
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentInvoiceInfoBinding
import com.divesh.perfex.sales.domain.adapters.invoices.InvoiceInfoItemsAdapter
import com.divesh.perfex.sales.domain.models.invoices.InvoiceResponse
import com.divesh.perfex.sales.domain.models.invoices.ViewInvoiceResponseModel
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class InvoiceInfoFragment : Fragment() {
    private var _binding: FragmentInvoiceInfoBinding? = null
    private val binding get() = _binding!!
    private lateinit var invoice: InvoiceResponse.Invoice
    private lateinit var progressBar: ProgressBar
    private val viewModel: InvoiceInfoViewModel by viewModels()

    private val listAdapter = InvoiceInfoItemsAdapter(arrayListOf())
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        arguments?.let {
            invoice = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                it.getParcelable("invoice", InvoiceResponse.Invoice::class.java)!!
            }else{
                it.getParcelable("invoice")!!
            }
        }
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentInvoiceInfoBinding.inflate(inflater, container, false)
        initViews()
        setupClickListeners()
        observeViewModel()
        return binding.root
    }

    private fun setupClickListeners() {
        binding.menu.setOnClickListener {
            //creating a popup menu
            val popup = PopupMenu(binding.root.context, it)
            //inflating menu from xml resource
            popup.inflate(R.menu.invoice_info_menu)
            //adding click listener
            popup.setOnMenuItemClickListener { item ->
                when (item.itemId) {
                    R.id.action_view_invoice_as_customer -> viewInvoiceAsCustomer()
                    R.id.action_mark_as_sent -> markInvoiceAsSent()
                    else -> false
                }
            }
            //displaying the popup
            popup.show()
        }
        binding.edit.setOnClickListener {  }
        binding.pdf.setOnClickListener {
            val url = "${Constants.API_URL}/view_invoice_pdf?staffid=${viewModel.staffId.value}&invoiceId=${invoice.id}&output_type=I"
            context?.let { it1 -> Extensions().viewPdf(it1, url, invoice.companyName.toString(), true) }
        }
        binding.sendMail.setOnClickListener {

        }
    }

    private fun markInvoiceAsSent(): Boolean {
        Extensions().simpleAlert(getString(R.string.confirm_mark_as_sent), requireContext()) { isConfirmed ->
            if (isConfirmed) {
                viewModel.markInvoiceAsSent(invoice.id)
            }
        }
        return false
    }

    private fun viewInvoiceAsCustomer(): Boolean {
        context?.let {
            Extensions().openUrl("${Constants.BASE_URL}/invoice/${invoice.id}/${invoice.hash}",
                it
            )
        }
        return true
    }

    private fun initViews() {
        progressBar = binding.progressBar
        binding.recyclerView.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }
    }

    private fun observeViewModel() {
        viewModel.responseMessage.observe(viewLifecycleOwner) { isError ->
            if (!isError.equals("") && isError != null) {
                progressBar.visibility = View.GONE
                Extensions().showMessage(binding.root, isError.toString())
            }
        }

        viewModel.loading.observe(viewLifecycleOwner) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }

        viewModel.internetProblem.observe(viewLifecycleOwner) {
            if (it != null) {
                if (it) {
                    Extensions().showMessage(binding.root, getString(R.string.no_internet))
                }
            }
        }

        viewModel.responseBody.observe(viewLifecycleOwner) {
            val response: ViewInvoiceResponseModel = it as ViewInvoiceResponseModel
            setInvoiceData(response)
        }
    }

    private fun setInvoiceData(response: ViewInvoiceResponseModel) {
        try {
            val status = InvoiceStatus.values()[(response.data.invoice.status + 1)]
            binding.statusChip.text = status.toString()
            when (status) {
                InvoiceStatus.UNPAID ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.FerrariRed)
                InvoiceStatus.PAID ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.ForestGreen)
                InvoiceStatus.PARTIALLY ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.OrangeSalmon)
                InvoiceStatus.OVERDUE ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.Yellow)
                InvoiceStatus.CANCELLED ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.BlushRed)
                InvoiceStatus.DRAFT ->
                    binding.statusChip.backgroundTintList =
                        ContextCompat.getColorStateList(binding.root.context, R.color.Beer)
            }
        } catch (_: IndexOutOfBoundsException) {

        }
        binding.invoiceNumber.text = invoice.invoiceNumber
        binding.invoiceDate.text = invoice.datecreated
        binding.dueDate.text = invoice.fancyDueDate
        binding.billTo.text = invoice.companyName
        binding.address.text = invoice.shipping_street
        var companyInformation = ""
        if(response.data.companyInformation.invoice_company_name != null){
            companyInformation += "${response.data.companyInformation.invoice_company_name}\n\n"
        }
        if(response.data.companyInformation.invoice_company_address != null){
            companyInformation += "${response.data.companyInformation.invoice_company_address}\n\n"
        }
        if(response.data.companyInformation.invoice_company_city != null){
            companyInformation += "${response.data.companyInformation.invoice_company_city}\n"
        }
        if(response.data.companyInformation.invoice_company_country_code != null){
            companyInformation += "${response.data.companyInformation.invoice_company_country_code}\n"
        }
        if(response.data.companyInformation.invoice_company_postal_code != null){
            companyInformation += "${response.data.companyInformation.invoice_company_postal_code}\n"
        }
        if(response.data.companyInformation.invoice_company_phonenumber != null){
            companyInformation += "${response.data.companyInformation.invoice_company_phonenumber}\n"
        }
        binding.companyInformation.text = companyInformation
        binding.subTotalAmount.text = invoice.subtotal.toString()
        binding.totalAmount.text = invoice.total.toString()
        binding.totalPaid.text = response.data.total_paid.toString()
        binding.totalAmount.text = response.data.amount_due.toString()

        if(response.data.invoice.items.isNotEmpty()){
            listAdapter.setRecords(response.data.invoice.items)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }

    override fun onResume() {
        super.onResume()
        viewModel.refresh(invoice.id)
    }

    companion object {
        @JvmStatic
        fun newInstance(invoice: InvoiceResponse.Invoice?) =
            InvoiceInfoFragment().apply {
                arguments = Bundle().apply {
                    putParcelable("invoice", invoice)
                }
            }
    }
}