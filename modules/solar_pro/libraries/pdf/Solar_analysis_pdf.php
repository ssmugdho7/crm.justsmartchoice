<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once(APPPATH . 'libraries/pdf/Sales_document_pdf.php');

class Solar_analysis_pdf extends Sales_document_pdf
{
    protected $analysis;
    public function __construct($analysis)
    {
        $this->analysis = $analysis;
        parent::__construct();
        $this->SetTitle(_l('solar_pro_your_solar_report') . ' #' . (int)$analysis['id']);
        $this->SetDisplayMode('default','OneColumn');
    }
    public function prepare()
    {
        $CI=&get_instance();
        $CI->load->helper('solar_pro/solar_pro');
        $this->set_view_vars([
            'analysis'=>$this->analysis,
            'months'=>solar_pro_month_names(),
            'finance'=>solar_pro_finance_summary($this->analysis),
        ]);
        return $this->build();
    }
    protected function presentationDocument(){ return (object)['id'=>$this->analysis['id'],'date'=>substr($this->analysis['created_at']??date('Y-m-d'),0,10)]; }
    protected function presentationNumber(){ return _l('solar_pro_your_solar_report').' #'.(int)$this->analysis['id']; }
    protected function presentationSubject(){ return $this->presentationNumber(); }
    protected function presentationCustomer(){ return trim($this->analysis['first_name'].' '.$this->analysis['last_name']); }
    protected function type(){ return 'solar-analysis'; }
    protected function file_path(){ return module_dir_path('solar_pro','views/pdf/analysis.php'); }
}
