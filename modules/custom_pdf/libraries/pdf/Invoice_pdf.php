<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'libraries/pdf/Sales_document_pdf.php';

class Invoice_pdf extends Sales_document_pdf
{
    protected $invoice;


    private $invoice_number;


    public function __construct($invoice, $tag = '')
    {
        $this->load_language($invoice->clientid);
        $invoice                = hooks()->apply_filters('invoice_html_pdf_data', $invoice);
        $GLOBALS['invoice_pdf'] = $invoice;

        parent::__construct();

        if (!class_exists('Invoices_model', false)) {
            $this->ci->load->model('invoices_model');
        }

        $this->tag            = $tag;
        $this->invoice        = $invoice;
        $this->invoice_number = format_invoice_number($this->invoice->id);


        $this->SetTitle($this->invoice_number);

    }

    public function prepare()
    {
        $this->with_number_to_word($this->invoice->clientid);

        $this->set_view_vars([
            'status'         => $this->invoice->status,
            'invoice_number' => $this->invoice_number,
            'payment_modes'  => $this->get_payment_modes(),
            'invoice'        => $this->invoice,
        ]);

        return $this->build();
    }

    // Page header
    protected function renderSalesBodyHeader()
    {
        $header_text = parsePDFMergeFields('invoice', getPdfOptions('invoice', 'header', 'text'), $this->invoice);
        $image_file  = custom_pdf_uploaded_image_path('invoice', getPdfOptions('invoice', 'header', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, 0, $this->getPageDimensions()['wk'], 30);
        }

        if ($header_text !== '') {
            $this->writeHTMLCell(0, 0, 10, 12, $header_text, 0, 0, 0, true, '', true);
        }

        $this->SetTopMargin(35);
    }

    // Page footer
    protected function renderSalesBodyFooter()
    {
        $footer_text = parsePDFMergeFields('invoice', getPdfOptions('invoice', 'footer', 'text'), $this->invoice);
        $image_file  = custom_pdf_uploaded_image_path('invoice', getPdfOptions('invoice', 'footer', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, $this->getPageHeight() - 30, $this->getPageWidth(), 30);
        }

        if ($footer_text !== '') {
            $this->writeHTMLCell(0, 0, 10, -12, $footer_text, 0, 0, 0, true, '', true);
        }

        $this->SetFooterMargin(35);
    }

    protected function type()
    {
        return 'invoice';
    }

    protected function file_path()
    {
        $customPath = APPPATH.'views/themes/'.active_clients_theme().'/views/my_invoicepdf.php';
        $actualPath = module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_invoice_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }

    private function get_payment_modes()
    {
        $this->ci->load->model('payment_modes_model');
        $payment_modes = $this->ci->payment_modes_model->get();

        // In case user want to include {invoice_number} or {client_id} in PDF offline mode description
        foreach ($payment_modes as $key => $mode) {
            if (isset($mode['description'])) {
                $payment_modes[$key]['description'] = str_replace('{invoice_number}', $this->invoice_number, $mode['description']);
                $payment_modes[$key]['description'] = str_replace('{client_id}', $this->invoice->clientid, $mode['description']);
            }
        }

        return $payment_modes;
    }
}
