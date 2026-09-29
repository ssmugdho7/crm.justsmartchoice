<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_dispatch_test_notification extends App_mail_template
{
    protected $for = 'staff';
    public $slug = 'appointly-dispatch-test-notification';
    public $rel_type = 'appointly';
    protected $staff;
    protected $message;

    public function __construct($staff, $message)
    {
        parent::__construct();
        $this->staff = $staff;
        $this->message = $message;
        $this->set_merge_fields('staff_merge_fields', $this->staff->staffid);
        $this->set_merge_fields([
            '{appointly_test_message}' => nl2br(html_escape($this->message)),
            '{appointly_dispatch_url}' => admin_url('appointly/smartchoice/dispatch'),
        ]);
    }

    public function build()
    {
        $this->to($this->staff->email);
    }
}
