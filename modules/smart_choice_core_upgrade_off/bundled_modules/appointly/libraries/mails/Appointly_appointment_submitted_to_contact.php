<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_appointment_submitted_to_contact extends App_mail_template
{
    public $slug = 'appointment-submitted-to-contact';
    protected $for = 'customer';
    protected $appointment;

    public function __construct($appointment)
    {
        parent::__construct();

        $this->appointment = $appointment;

        // Set merge fields for email
        $this->set_merge_fields('appointly_merge_fields', $this->appointment->id);
    }

    public function build()
    {
        $this->to($this->appointment->email);
    }
}
