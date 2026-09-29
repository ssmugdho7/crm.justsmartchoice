<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Recruitment_skill_test_internal extends App_mail_template
{
    protected $result;
    public $slug = 'recruitment-skill-test-internal';

    public function __construct($result)
    {
        parent::__construct();
        $this->result = $result;
        $this->set_merge_fields('smart_choice_job_application_merge_fields', $result);
    }

    public function build()
    {
        $recipient = !empty($this->result->recipient)
            ? $this->result->recipient
            : (get_option('recruitment_skill_test_email') ?: 'employees@justasmartchoice.com');
        $this->to($recipient);
    }
}
