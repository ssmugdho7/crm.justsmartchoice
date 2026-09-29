<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Smart_choice_new_job_application_internal extends App_mail_template
{
    protected $candidate; public $slug='smart-choice-new-job-application-internal';
    public function __construct($candidate){ parent::__construct(); $this->candidate=$candidate; $this->set_merge_fields('smart_choice_job_application_merge_fields',$candidate); }
    public function build(){ $recipients=get_option('smart_choice_recruitment_internal_recipients'); if (!$recipients) { $recipients=get_option('smtp_email'); } $this->to($recipients); }
}
