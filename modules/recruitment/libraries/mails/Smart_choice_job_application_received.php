<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_job_application_received extends App_mail_template
{
    protected $candidate; public $slug='smart-choice-job-application-received';
    public function __construct($candidate){ parent::__construct(); $this->candidate=$candidate; $this->set_merge_fields('smart_choice_job_application_merge_fields',$candidate); if (!empty($candidate->email)) { $this->set_rel_id(0); } }
    public function build(){ $this->to($this->candidate->email); }
}
