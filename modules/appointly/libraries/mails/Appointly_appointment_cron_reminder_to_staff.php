<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_cron_reminder_to_staff extends App_mail_template
{
    public $slug = 'appointment-cron-reminder-to-staff';
    protected $for = 'staff';
    protected $appointment;
    protected $staff;

    public function __construct(object $appointment, object $staff)
    {
        parent::__construct();

        $this->staff = $staff;
        $this->appointment = $appointment;

        // For SMS and merge fields for email
        $this->set_merge_fields('staff_merge_fields', $this->staff->staffid);
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build(): void
    {
        $this->to($this->staff->email);
    }
}
