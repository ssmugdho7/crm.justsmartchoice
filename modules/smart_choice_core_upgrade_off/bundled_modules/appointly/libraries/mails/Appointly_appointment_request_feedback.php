<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_request_feedback extends App_mail_template
{
    protected $for = 'contact';

    public $slug = 'appointly-appointment-request-feedback';

    protected $appointment;

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;

        // For SMS and merge fields for email
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }
    public function build()
    {
        // Added null check to prevent passing null to strpos() in email validation
        if (isset($this->appointment->email) && !is_null($this->appointment->email)) {
            $this->to($this->appointment->email);
        }
    }
}
