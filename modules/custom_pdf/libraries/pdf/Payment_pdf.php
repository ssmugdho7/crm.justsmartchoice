<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH.'libraries/pdf/Sales_document_pdf.php';

class Payment_pdf extends Sales_document_pdf
{
    protected $payment;

    protected $page_width;
    protected $page_height;

    public function __construct($payment, $tag = '')
    {
        $GLOBALS['payment_pdf'] = $payment;

        $this->load_language($payment->invoice_data->clientid);

        parent::__construct();

        if (!class_exists('payments_model', false)) {
            $this->ci->load->model('payments_model');
        }

        $this->page_width  = $this->getPageDimensions()['wk'];
        $this->page_height = $this->getPageDimensions()['hk'];

        $this->payment = $payment;
        $this->tag     = $tag;

        $this->SetTitle(_l('payment') . ' #' . $this->payment->paymentid);
    }

    public function prepare()
    {
        $amountDue = ($this->payment->invoice_data->status != Invoices_model::STATUS_PAID && $this->payment->invoice_data->status != Invoices_model::STATUS_CANCELLED ? true : false);

        $this->set_view_vars([
            'payment'   => $this->payment,
            'amountDue' => $amountDue,
        ]);

        return $this->build();
    }

    // Page header
    protected function renderSalesBodyHeader()
    {
        $header_text = parsePDFMergeFields('payment', getPdfOptions('payment', 'header', 'text'), $this->payment);
        $image_file  = custom_pdf_uploaded_image_path('payment', getPdfOptions('payment', 'header', 'image'));

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
        $footer_text = parsePDFMergeFields('payment', getPdfOptions('payment', 'footer', 'text'), $this->payment);
        $image_file  = custom_pdf_uploaded_image_path('payment', getPdfOptions('payment', 'footer', 'image'));

        if ($image_file !== '') {
            $this->Image($image_file, 0, $this->page_height - 30, $this->page_width, 30);
        }

        if ($footer_text !== '') {
            $this->writeHTMLCell(0, 0, 10, -12, $footer_text, 0, 0, 0, true, '', true);
        }

        $this->SetFooterMargin(35);
    }

    protected function type()
    {
        return 'payment';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_paymentpdf.php';
        $actualPath = module_views_path(CUSTOM_PDF_MODULE, 'pdf_template/custom_payment_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
