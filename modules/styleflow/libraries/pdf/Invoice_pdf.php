<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'libraries/pdf/Sales_document_pdf.php';

class Invoice_pdf extends Sales_document_pdf
{
    protected $invoice;
    private $document_number;

    public function __construct($invoice, $tag = '')
    {
        $this->load_language($invoice->clientid);
        $invoice = hooks()->apply_filters('invoice_html_pdf_data', $invoice);
        $GLOBALS['invoice_pdf'] = $invoice;
        parent::__construct();
        if (!class_exists('Invoices_model', false)) $this->ci->load->model('invoices_model');
        $this->tag = $tag;
        $this->invoice = $invoice;
        $this->document_number = format_invoice_number($invoice->id);
        $this->SetTitle($this->document_number);
    }

    public function prepare()
    {
        $this->with_number_to_word($this->invoice->clientid);
        $this->set_view_vars([
            'document'=>$this->invoice,
            'document_type'=>'invoice',
            'document_number'=>$this->document_number,
            'status'=>$this->invoice->status,
        ]);
        return $this->build();
    }
    protected function type(){ return 'invoice'; }
    protected function file_path(){ return APP_MODULES_PATH . 'styleflow/views/pdf/styleflow_document.php'; }
}
