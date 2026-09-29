<?php

defined('BASEPATH') or exit('No direct script access allowed');
include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Proposal_pdf extends App_pdf
{
    protected $proposal;
    private $document_number;
    public function __construct($proposal, $tag = '')
    {
        $clientId = ($proposal->rel_type === 'customer') ? (int)$proposal->rel_id : 0;
        if ($clientId) $this->load_language($clientId);
        $proposal = hooks()->apply_filters('proposal_html_pdf_data', $proposal);
        $GLOBALS['proposal_pdf'] = $proposal;
        parent::__construct();
        $this->tag=$tag; $this->proposal=$proposal;
        $this->document_number = format_proposal_number($proposal->id);
        $this->SetTitle($this->document_number);
    }
    public function prepare()
    {
        $clientId = ($this->proposal->rel_type === 'customer') ? (int)$this->proposal->rel_id : 0;
        if ($clientId) $this->with_number_to_word($clientId);
        $this->set_view_vars(['document'=>$this->proposal,'document_type'=>'proposal','document_number'=>$this->document_number,'status'=>$this->proposal->status]);
        return $this->build();
    }
    protected function type(){ return 'proposal'; }
    protected function file_path(){ return APP_MODULES_PATH . 'styleflow/views/pdf/styleflow_document.php'; }
}
