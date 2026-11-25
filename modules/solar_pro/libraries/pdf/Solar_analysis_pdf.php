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
    protected function renderPresentationPage($section)
    {
        // Reuse the estimate's configured cover and closing artwork, with the shared page lifecycle.
        $settings=json_decode((string)get_option('estimate_pdf_settings'),true);
        $name=(string)($settings[$section]['image']??'');
        $root=realpath(FCPATH.'uploads/custom_pdf/estimate');
        $path=$root&&$name!==''&&basename($name)===$name?realpath($root.'/'.$name):false;
        if(!$path||strpos($path,$root.DIRECTORY_SEPARATOR)!==0||!is_readable($path)||!@getimagesize($path)){parent::renderPresentationPage($section);return;}
        $this->renderingPresentation=true;$this->presentationPages[$this->page]=true;
        $margin=$this->getBreakMargin();$auto=$this->getAutoPageBreak();$this->SetAutoPageBreak(false,0);
        $this->Image($path,0,0,$this->getPageWidth(),$this->getPageHeight());
        $this->SetAutoPageBreak($auto,$margin);$this->setPageMark();$this->renderingPresentation=false;
    }
    protected function presentationDocument(){ return (object)['id'=>$this->analysis['id'],'date'=>substr($this->analysis['created_at']??date('Y-m-d'),0,10)]; }
    protected function presentationNumber(){ return _l('solar_pro_your_solar_report').' #'.(int)$this->analysis['id']; }
    protected function presentationSubject(){ return $this->presentationNumber(); }
    protected function presentationCustomer(){ return trim($this->analysis['first_name'].' '.$this->analysis['last_name']); }
    protected function type(){ return 'solar-analysis'; }
    protected function file_path(){ return module_dir_path('solar_pro','views/pdf/analysis.php'); }
}
