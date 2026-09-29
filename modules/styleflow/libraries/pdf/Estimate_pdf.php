<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Estimate_pdf extends App_pdf
{
    protected $estimate;
    private $document_number;
    public function __construct($estimate, $tag = '')
    {
        $this->load_language($estimate->clientid);
        $estimate = hooks()->apply_filters('estimate_html_pdf_data', $estimate);
        $GLOBALS['estimate_pdf'] = $estimate;
        parent::__construct();
        if (!class_exists('Estimates_model', false)) $this->ci->load->model('estimates_model');
        $this->tag=$tag; $this->estimate=$estimate;
        $this->document_number = format_estimate_number($estimate->id);
        $this->SetTitle($this->document_number);
    }
    public function prepare()
    {
        $this->with_number_to_word($this->estimate->clientid);
        $this->set_view_vars(['document'=>$this->estimate,'document_type'=>'estimate','document_number'=>$this->document_number,'status'=>$this->estimate->status]);
        return $this->build();
    }
    protected function type(){ return 'estimate'; }
    protected function file_path(){ return APP_MODULES_PATH . 'styleflow/views/pdf/styleflow_document.php'; }
}
