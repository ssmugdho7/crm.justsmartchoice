<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Solar_proposal_pdf extends App_pdf
{
    protected $proposal;

    public function __construct($proposal)
    {
        parent::__construct();
        $this->proposal = $proposal;
        $this->SetTitle((string) ($proposal['title'] ?? _l('solar_pro_solar_proposal')));
        $this->SetDisplayMode('default', 'OneColumn');
    }

    public function prepare()
    {
        $analysis = $this->proposal['analysis'];
        $this->set_view_vars([
            'proposal'    => $this->proposal,
            'analysis'    => $analysis,
            'finance'     => solar_pro_finance_summary($analysis),
            'environment' => solar_pro_environment_summary($analysis),
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'solar-proposal';
    }

    protected function file_path()
    {
        return module_dir_path('solar_pro', 'views/pdf/proposal.php');
    }
}
