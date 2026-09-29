<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_completed_to_staff extends App_mail_template
{
    protected $staff;
    protected $appointment;

    public $slug = 'appointment-completed-to-staff';
    public $rel_type = 'appointly';

    public function __construct($staff, $appointment)
    {
        parent::__construct();

        $this->staff = $staff;
        $this->appointment = $appointment;

        // Set merge fields
        $this->set_rel_id($this->appointment->id);
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
        $this->set_merge_fields('staff_merge_fields', $this->staff->staffid);
    }

    public function build()
    {
        $this->to($this->staff->email);
    }
}
