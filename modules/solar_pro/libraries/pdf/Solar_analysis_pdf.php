<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Solar_analysis_pdf extends App_pdf
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
    protected function type(){ return 'solar-analysis'; }
    protected function file_path(){ return module_dir_path('solar_pro','views/pdf/analysis.php'); }
}
